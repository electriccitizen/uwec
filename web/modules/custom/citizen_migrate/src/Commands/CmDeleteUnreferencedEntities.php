<?php

namespace Drupal\citizen_migrate\Commands;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 */
class CmDeleteUnreferencedEntities extends DrushCommands {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;


  /**
   * Constructs a new CmDeleteUnreferencedEntities object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager, FileSystemInterface $fileSystem) {
    $this->entityTypeManager = $entityTypeManager;
    $this->fileSystem = $fileSystem;
  }

  /**
   * Deletes unreferenced file or media entities based on the migration map table.
   *
   * @param string $migration_name
   *   The name of the migration (e.g., files_img or media_img).
   * @command citizen_migrate:delete_unreferenced
   * @aliases cmdu
   * @usage citizen_migrate:delete_unreferenced files_img
   *   Delete unreferenced file entities based on migrate_map_files_img table.
   * @usage citizen_migrate:delete_unreferenced media_img
   *   Delete unreferenced media entities based on migrate_map_media_img table.
   */
  public function deleteUnreferenced($migration_name) {
    $csv_path = 'public://source-data/active_image_ids.csv';
    $real_path = $this->fileSystem->realpath($csv_path);

    /** @var \Drupal\citizen_migrate\Services\CmTools $cmTools */
    $cmTools = \Drupal::service('cm.tools');

    if (!file_exists($real_path)) {
      $this->logger()->error(dt('CSV file does not exist at @path.', ['@path' => $real_path]));
      return;
    }

    // Determine table name and entity type based on migration name
    $table_name = "migrate_map_{$migration_name}";
    $entity_type = $migration_name == 'files_img' ? 'file' : 'media';

    // Load CSV data into an array
    $csv_data = [];
    if (($handle = fopen($real_path, 'r')) !== FALSE) {
      while (($data = fgetcsv($handle, 1000, ',')) !== FALSE) {
        $csv_data[] = trim($data[4]);
      }
      fclose($handle);
    }

    $query = \Drupal::database()->select($table_name, 'm')
      ->fields('m', ['sourceid1', 'destid1']);
    $results = $query->execute();

    foreach ($results as $row) {
      if (!in_array($row->sourceid1, $csv_data)) {
        // If sourceid1 is not in the CSV, delete the respective entity
        $entity = $this->entityTypeManager->getStorage($entity_type)->load($row->destid1);
        if ($entity) {
          $entity->delete();
          $this->logger()->info('Deleted @type entity with ID @id.', ['@type' => $entity_type, '@id' => $row->destid1]);
        }
      }
    }
  }
}