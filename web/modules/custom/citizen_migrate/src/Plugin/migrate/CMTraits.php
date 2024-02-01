<?php

namespace Drupal\citizen_migrate\Plugin\migrate;

use Drupal\Core\Database\Database;
use Drupal\Core\File\FileSystemInterface;
use Drupal\migrate\MigrateException;
use Drush\Drush;

trait CMTraits {

  /**
   * Prints to the console for development and debugging.
   *
   * @param $var mixed
   *   The value to print to the console.
   *
   * @return mixed
   *   String values.
   */
  protected function debug($var) {
    switch (true) {
      case gettype($var) != 'string':
        return Drush::output()->writeln(print_r($var));

      default:
        return Drush::output()->writeln($var);
    }
  }

  /**
   * Get the temporary storage service to hold data for post-migration logging.
   *
   * @param $storage_name string
   *   Arbitrary name which sets the key by which the data can be retrieved.
   *
   * @return mixed
   */
  protected function getStorage(string $storage_name = 'citizen_migrate') {
    return \Drupal::service('tempstore.private')->get($storage_name);
  }

  /**
   * Returns a connection to the legacy database.
   *
   * @return \Drupal\Core\Database\Connection
   */
  protected function getLegacyDatabase() {
    return Database::getConnection('default', 'migrate');
  }

  /**
   * Returns the UUID for a given new Media ID of an import.
   *
   * @param $mid
   *
   * @return mixed
   */
  protected function getUuid($mid) {
    $query = \Drupal::database()->select('media', 'm');
    $query->fields('m', ['uuid']);
    $query->condition('m.mid', $mid);

    return $query->execute()->fetchField();
  }

  /**
   * Returns the UUID for a given legacy source Media ID.
   *
   * @param string|int $old_id
   *
   * @return mixed
   */
  protected function legacyUuidLookup($migration_names, $old_id, $media_type): mixed {
    // Lookup the file uri using the original fid.
    $file_path = '';
    if (str_contains($old_id, 'youtu')) {
      // $old_id contains a video URL.
      $file_path = $old_id;
    }
    else {
      // $old_id value is the fid on the legacy system.
      $ldb = $this->getLegacyDatabase();
      $uri = $ldb->select('file_managed', 'fm')
        ->fields('fm', ['uri'])
        ->condition('fm.fid', $old_id)
        ->execute()
        ->fetchField();
      // Modify the uri to reflect the actual file path.
      $file_path = str_replace('public://', 'sites/default/files/', $uri);
    }

    $mid = 0;
    foreach ($migration_names as $migration_name) {
      $mid = $this->lookup($migration_name, $file_path);
    }
    $this->debug(['file_path' => $file_path, 'mid' => $mid]);
    $uuid = null;
    if (!empty($mid)) {
      $uuid = \Drupal::database()->select('media', 'm')
        ->fields('m', ['uuid'])
        ->condition('m.mid', $mid[0]['mid'])
        ->execute()
        ->fetchField();

      return $uuid;
    }

    // If no uuid was found, log the file data to a file.
    if (!$uuid) {
      $data['fid'] = $old_id;
      $data['type'] = $media_type;
      $data['file_path'] = $file_path;
      $this->logToFile('missing_media.csv', $data);
    }
    return '';
  }

  /**
   * Lookup the new ID from the legacy source Media ID.
   *
   * @param string|int $old_id
   *
   * @return mixed
   */
  protected function lookup($migration_names, $old_id) {
    return \Drupal::service('migrate.lookup')->lookup($migration_names, [$old_id]);
  }

  /**
   * Returns the markup for the list of images from a list of source FIDs.
   *
   * @param $images
   *
   * @return string
   */
  protected function getImageMarkup($images):string {
    $markup = '<h2>Images</h2>';

    foreach ($images as $image) {
      // Get the new UUID for the imported media id.
      $uuid = $this->uuidLookup('media_img', $image['fid'], 'image');

      // Add the image media to the $image_string.
      $markup .= "<drupal-media data-entity-type=\"media\" data-entity-uuid=\"$uuid\" data-view-mode=\"non_cropped_lg\"></drupal-media>";
    }

    return $markup;
  }

  /**
   * Returns the markup for the list of attachments and links from source FIDs (attachments).
   *
   * @param array $attachments
   * @param array $links
   *
   * @return string
   */
  protected function getLinkAttachmentMarkup(array $attachments, array $links):string {

    $markup = '<h2>Links</h2><ul>';

    // Add the attachments.
    if (!empty($attachments)) {
      foreach ($attachments as $attachment) {
        // Get the new UUID for the imported media id.
        $uuid = $this->uuidLookup('media_doc', $attachment['fid'], 'document');

        // Add the attachment to the $link_string.
        $markup .= "<li><drupal-media data-entity-type=\"media\" data-entity-uuid=\"$uuid\" data-view-mode=\"\"></drupal-media></li>";
      }
    }

    // Add the links.
    if (!empty($links)) {
      foreach ($links as $link) {
        $url = $link['url'] ?? '';
        $title = $link['title'] ?? '';
        $markup .= "<li><a href=\"$url\">$title</a></li>";
      }
    }

    $markup .= '</ul>';

    return $markup;
  }

  /**
   * Returns the markup for the embedded video from the source video_url.
   *
   * @param array $video
   *
   * @return string
   */
  protected function getVideoMarkup(array $videos):string {
    $markup = "<h2>Video</h2>";

    foreach ($videos as $video) {
      // Get the UUID for the imported video media.
      $uuid = $this->uuidLookup('media_video', $video['video_url'], 'video');

      // Add the video media to the markup.
      $markup .= "<drupal-media data-align=\"center\" data-entity-type=\"media\" data-entity-uuid=\"$uuid\"></drupal-media>";
    }
    return $markup;
  }

  /**
   * Print output to a CSV file in the public://migration-logs directory.
   *
   * @param string $filename
   *   Required nome of the file and extension.
   * @param array $data
   *   Required array of items to be printed into each column.
   * @param string $message
   *   Optional message to print to the console for confirmation.
   *
   * @return void
   * @throws \Drupal\migrate\MigrateException
   */
  protected function logToFile(string $filename, array $data, string $message = '') {
    $fs = \Drupal::service('file_system');

    $file_path = 'public://migration-logs/' . $filename;

    // Ensure the 'migrate-logs' directory exists, create it if not.
    $logfile = $fs->getDestinationFilename($file_path, 'use existing');
    // Try opening the file. Suppress errors until after attempting prepareDirectory.
    $filestream = @fopen($logfile, 'a');
    if (!$filestream) {
      // Ensure there is a writable directory.
      $dir = $fs->dirname($file_path);
      if (!$fs->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
        throw new MigrateException("Could not create or write to the directory '$dir'");
      }
      // Try again to open the file.
      $filestream = @fopen($logfile, 'a');
      if (!$filestream) {
        throw new MigrateException("Could not write to file '$file_path'");
      }
    }

    // Log the date of this row.
    $data['date'] = date("Y-m-d H:i:s");

    $logFile = fopen($file_path, 'a');
    fputcsv($logFile, $data);
    fclose($logFile);

    // Log to the console for verification.
    if (!empty($message)) {
      $this->debug($message);
    }

  }
}
