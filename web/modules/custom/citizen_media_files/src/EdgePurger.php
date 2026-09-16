<?php

namespace Drupal\citizen_media_files;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Site\Settings;
use Drupal\file\FileInterface;
use Psr\Log\LoggerInterface;

/**
 * Purges file URLs from the hosting platform's edge cache.
 *
 * Host support:
 * - Acquia: queues "url" invalidations through the purge module, which the
 *   site's configured purgers (Acquia Platform CDN and/or Varnish) process.
 * - Pantheon: calls pantheon_clear_edge_paths(), provided by the platform.
 * - Anything else (including local): logs what would have been purged.
 */
class EdgePurger {

  /**
   * Host detector.
   *
   * @var \Drupal\citizen_media_files\HostDetector
   */
  protected $hostDetector;

  /**
   * Module logger channel.
   *
   * @var \Psr\Log\LoggerInterface
   */
  protected $logger;

  /**
   * File URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * Entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Site settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Constructs the purger.
   */
  public function __construct(HostDetector $host_detector, LoggerInterface $logger, FileUrlGeneratorInterface $file_url_generator, EntityTypeManagerInterface $entity_type_manager, Settings $settings) {
    $this->hostDetector = $host_detector;
    $this->logger = $logger;
    $this->fileUrlGenerator = $file_url_generator;
    $this->entityTypeManager = $entity_type_manager;
    $this->settings = $settings;
  }

  /**
   * Purges a file's URL (and any image style derivatives) from the edge.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file whose URLs should be purged.
   * @param string $reason
   *   Short human-readable trigger description, used in log messages.
   */
  public function purgeFile(FileInterface $file, string $reason = 'file change'): void {
    $uri = $file->getFileUri();
    // Only public files are served through the edge cache.
    if (!str_starts_with((string) $uri, 'public://')) {
      return;
    }
    $urls = $this->expandUrls($this->collectUrls($file));
    if (!$urls) {
      return;
    }
    switch ($this->hostDetector->getHost()) {
      case HostDetector::ACQUIA:
        $this->purgeAcquia($urls, $reason);
        break;

      case HostDetector::PANTHEON:
        $this->purgePantheon($urls, $reason);
        break;

      default:
        $this->logger->info('No edge cache host detected; would purge (@reason): @urls', [
          '@reason' => $reason,
          '@urls' => $this->summarizeUrls($urls),
        ]);
    }
  }

  /**
   * Builds the absolute URLs for a file, including image style derivatives.
   *
   * @param \Drupal\file\FileInterface $file
   *   The file.
   *
   * @return string[]
   *   Absolute URLs.
   */
  protected function collectUrls(FileInterface $file): array {
    $uri = $file->getFileUri();
    $urls = [$this->fileUrlGenerator->generateAbsoluteString($uri)];
    // Image derivatives live at their own edge-cached URLs. Only include
    // derivatives that actually exist on disk: a style that was never
    // generated was never served, and sites carry 100+ styles. Both MEFR
    // (which flushes derivatives AFTER the file entity save that triggers
    // this) and file deletion (image module's flush hook runs after ours,
    // by module name order) leave derivatives on disk at hook time.
    if (str_starts_with((string) $file->getMimeType(), 'image/')) {
      try {
        $styles = $this->entityTypeManager->getStorage('image_style')->loadMultiple();
        foreach ($styles as $style) {
          /** @var \Drupal\image\ImageStyleInterface $style */
          if ($style->supportsUri($uri) && file_exists($style->buildUri($uri))) {
            $urls[] = $style->buildUrl($uri);
          }
        }
      }
      catch (\Exception $e) {
        $this->logger->warning('Could not build image style URLs for @uri: @message', [
          '@uri' => $uri,
          '@message' => $e->getMessage(),
        ]);
      }
    }
    return array_unique($urls);
  }

  /**
   * Queues URL invalidations through the purge module on Acquia.
   *
   * @param string[] $urls
   *   Absolute URLs to purge.
   * @param string $reason
   *   Trigger description for logging.
   */
  protected function purgeAcquia(array $urls, string $reason): void {
    // The purge module is an optional dependency (absent on Pantheon sites),
    // so its services cannot be constructor-injected here.
    // phpcs:ignore DrupalPractice.Objects.GlobalDrupal.GlobalDrupal
    $container = \Drupal::getContainer();
    if (!$container->has('purge.invalidation.factory') || !$container->has('purge.queue') || !$container->has('purge.queuers')) {
      $this->logger->warning('Acquia host detected but the purge module is not available; NOT purged (@reason): @urls', [
        '@reason' => $reason,
        '@urls' => $this->summarizeUrls($urls),
      ]);
      return;
    }
    try {
      $queuer = $container->get('purge.queuers')->get('citizen_media_files');
      if (!$queuer) {
        $this->logger->warning('The citizen_media_files purge queuer is not enabled; NOT purged: @urls', [
          '@urls' => $this->summarizeUrls($urls),
        ]);
        return;
      }
      $factory = $container->get('purge.invalidation.factory');
      $invalidations = [];
      foreach ($urls as $url) {
        $invalidations[] = $factory->get('url', $url);
      }
      $container->get('purge.queue')->add($queuer, $invalidations);
      $this->logger->notice('Queued @count edge purge(s) (@reason): @urls', [
        '@count' => count($invalidations),
        '@reason' => $reason,
        '@urls' => $this->summarizeUrls($urls),
      ]);
    }
    catch (\Exception $e) {
      $this->logger->error('Failed to queue edge purges (@reason): @class @message. URLs not purged: @urls', [
        '@reason' => $reason,
        '@class' => get_class($e),
        '@message' => $e->getMessage(),
        '@urls' => $this->summarizeUrls($urls),
      ]);
    }
  }

  /**
   * Clears edge paths via Pantheon's platform function.
   *
   * @param string[] $urls
   *   Absolute URLs to purge.
   * @param string $reason
   *   Trigger description for logging.
   */
  protected function purgePantheon(array $urls, string $reason): void {
    if (!function_exists('pantheon_clear_edge_paths')) {
      $this->logger->warning('Pantheon host detected but pantheon_clear_edge_paths() is unavailable; NOT purged (@reason): @urls. The platform API may have changed.', [
        '@reason' => $reason,
        '@urls' => $this->summarizeUrls($urls),
      ]);
      return;
    }
    $paths = [];
    foreach ($urls as $url) {
      $parts = parse_url($url);
      $path = $parts['path'] ?? '';
      if (!empty($parts['query'])) {
        $path .= '?' . $parts['query'];
      }
      if ($path) {
        $paths[] = $path;
      }
    }
    try {
      // Pantheon caps how many paths one call accepts; chunk defensively.
      foreach (array_chunk($paths, 25) as $chunk) {
        pantheon_clear_edge_paths($chunk);
      }
      $this->logger->notice('Cleared @count Pantheon edge path(s) (@reason): @paths', [
        '@count' => count($paths),
        '@reason' => $reason,
        '@paths' => $this->summarizeUrls($paths),
      ]);
    }
    catch (\Exception $e) {
      $this->logger->error('Pantheon edge clear failed (@reason): @message', [
        '@reason' => $reason,
        '@message' => $e->getMessage(),
      ]);
    }
  }

  /**
   * Expands each URL across the site's real public base URLs.
   *
   * Varnish caches per scheme and host, so a purge only clears the exact
   * variant it names. URLs generated in CLI/cron context come out http:// —
   * purging that clears a redirect object while the real https:// file object
   * survives (confirmed on Acquia dev) — and multi-domain sites cache a copy
   * per domain. Sites list their public base URLs in settings.php:
   *
   * @code
   * $settings['citizen_media_files_base_urls'] = [
   *   'https://dcyf.mn.gov',
   *   'https://mndcyfprod.prod.acquia-sites.com',
   * ];
   * @endcode
   *
   * Without the setting, the generated URL's host is kept and the scheme is
   * forced to https.
   *
   * @param string[] $urls
   *   Absolute URLs as generated.
   *
   * @return string[]
   *   URLs expanded across base URLs.
   */
  protected function expandUrls(array $urls): array {
    $bases = $this->settings->get('citizen_media_files_base_urls') ?: [];
    $out = [];
    foreach ($urls as $url) {
      $parts = parse_url($url);
      $path_query = ($parts['path'] ?? '') . (isset($parts['query']) ? '?' . $parts['query'] : '');
      if ($bases) {
        foreach ($bases as $base) {
          $out[] = rtrim($base, '/') . $path_query;
        }
      }
      else {
        $host = ($parts['host'] ?? '') . (isset($parts['port']) ? ':' . $parts['port'] : '');
        $out[] = 'https://' . $host . $path_query;
      }
    }
    return array_unique($out);
  }

  /**
   * Renders a URL list for logging without flooding the log.
   *
   * @param string[] $urls
   *   URLs or paths.
   *
   * @return string
   *   The first few entries plus a count of the rest.
   */
  protected function summarizeUrls(array $urls): string {
    $shown = array_slice($urls, 0, 5);
    $rest = count($urls) - count($shown);
    $summary = implode(', ', $shown);
    if ($rest > 0) {
      $summary .= " (+ $rest more)";
    }
    return $summary;
  }

}
