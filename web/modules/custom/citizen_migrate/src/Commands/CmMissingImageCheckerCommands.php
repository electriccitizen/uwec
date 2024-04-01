<?php

namespace Drupal\citizen_migrate\Commands;

use Drupal\Core\Database\Database;
use Drush\Commands\DrushCommands;

/**
 * A Drush commandfile.
 */
class CmMissingImageCheckerCommands extends DrushCommands {

  /**
   * Checks for missing images and logs them to a CSV file.
   *
   * @command missing_images:check
   * @aliases mic-check
   * @usage missing_images:check
   *   Check for missing images and log them.
   */
  public function check() {
    $file_directory = DRUPAL_ROOT . '/sites/default/files/images';
    $csv_file = DRUPAL_ROOT . '/sites/default/files/missing_images.csv';

    // Open CSV file for writing
    $file_handle = fopen($csv_file, 'w');
    fputcsv($file_handle, ['sourceid1', 'filename']); // CSV headers

    /** @var \Drupal\citizen_migrate\Services\CmTools $cmTools */
    $cmTools = \Drupal::service('cm.tools');

    $connection = Database::getConnection();
    $query = $connection->select('file_managed', 'fm')
      ->fields('fm', ['fid', 'filename'])
      ->condition('uri', 'public://images%', 'LIKE');
    $result = $query->execute();

    while ($record = $result->fetchAssoc()) {
      $filepath = $file_directory . '/' . $record['filename'];
      if (file_exists($filepath)) {
        $sourceid1_query = $connection->select('migrate_map_files_img', 'mmfi')
          ->fields('mmfi', ['sourceid1'])
          ->condition('destid1', $record['fid'])
          ->execute();
        $sourceid1_record = $sourceid1_query->fetchAssoc();

        fputcsv($file_handle, [$sourceid1_record['sourceid1'], $record['filename']]);
      } else {
        $cmTools->logToConsole($filepath);
      }
    }

    fclose($file_handle);
    $this->output()->writeln("Check complete. Missing images logged to $csv_file");
  }

}
