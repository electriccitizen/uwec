<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_uri_validate"
 * )
 */
class CmUriValidate extends \Drupal\citizen_migrate\Plugin\migrate\CMProcess {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
//    $migrate_tools = \Drupal::service('city_migration.tools');
    $domain = $this->configuration['domain'];
    $log_dir = $this->configuration['log_dir'] ?? 'public://migration-logs';
    $log_file = $this->configuration['log_file'] ?? '404_errors.csv';
    $migration_name = $this->configuration['migration_name'];

    // Ping the server for the url headers.
    $http_response_header = $this->cmTools->validateUrl($value, $domain);

    // Log any errors.
    if (is_array($http_response_header)) {
      $response_code = $http_response_header['response_code'];
      $url = $http_response_header['url'];

      $data['migration_name']  = $migration_name;
      $data['url'] = $url;
      $data['response_code'] = $response_code;

      // Log the data in case of failure.
      if ($http_response_header == -1) {
        $data['error_message'] = "get_header() request failed.";
      }

      // Log the data in case of 404.
      if ($http_response_header == 404) {
        $data['error_message'] = "File doesn't exist on server";
      }

      $migrate_tools->logToFile($data, "$log_dir/$log_file", "");
      return $response_code;
    }
    return $value;
  }

}