<?php

namespace Drupal\citizen_migrate\Commands;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Filesystem\Filesystem;
use Drupal\Core\Image\ImageFactory;
use Drush\Commands\DrushCommands;
use Symfony\Component\Finder\Finder;

class CmResizeImagesCommand extends DrushCommands {
  /**
   * The image factory service.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  public function __construct(ImageFactory $imageFactory) {
    $this->imageFactory = $imageFactory;
  }

  /**
   * Resizes images in a directory if they exceed a certain file size.
   *
   * @param string $directory
   *   The directory to scan for images.
   * @param int $threshold
   *   The file size threshold in bytes.
   * @command citizen-migrate:resize-images
   * @aliases crsz
   * @usage citizen-migrate:resize-images /path/to/images 5000000
   *   Resize and backup images in "/path/to/images" directory if they are larger than 5MB.
   */
  public function resizeImages($directory, $threshold) {
    $filesystem = new Filesystem();
    $finder = new Finder();
    $finder->files()->in($directory)->size('> ' . $threshold);


    // Ensure the backup directory exists
    $backupDir = $directory . '/backup';
    if (!$filesystem->exists($backupDir)) {
      $filesystem->mkdir($backupDir);
    }

    $files = iterator_to_array($finder);
    if (count($files) === 0) {
      $this->logger()->notice('No images exceeding the size threshold were found.');
      return;
    }

    $progressBar = new ProgressBar($this->output(), count($files));
    $progressBar->start();

    foreach ($files as $file) {
      // Copy original file to backup directory
      $backupFilePath = $backupDir . '/' . $file->getFilename();
      $filesystem->copy($file->getRealPath(), $backupFilePath);

      $image = $this->imageFactory->get($file->getRealPath());
      if ($image->isValid()) {
        do {
          $originalWidth = $image->getWidth();
          $newWidth = $originalWidth * 0.9; // Reduce width by 10%
          $image->scale($newWidth); // Scale method maintains aspect ratio
          $image->save($file->getRealPath());
          $newFileSize = filesize($file->getRealPath());
          if ($newFileSize > $threshold) {
            $image = $this->imageFactory->get($file->getRealPath()); // Reload for further resizing if needed
          }
        } while ($newFileSize > $threshold);
      }
      $progressBar->advance();
    }

    $progressBar->finish();
    $this->logger()->notice('Image resizing and backup operation completed.');
  }
}
