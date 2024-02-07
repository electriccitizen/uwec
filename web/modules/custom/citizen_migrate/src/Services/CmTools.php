<?php

namespace Drupal\citizen_migrate\Services;

use Drupal\Core\Database\Connection;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Logger\LoggerChannelFactoryInterface;
use Drupal\migrate\MigrateException;
use Drush\Drush;

/**
 * Service description.
 */
class CmTools {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The file handler.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * The logger channel factory.
   *
   * @var \Drupal\Core\Logger\LoggerChannelFactoryInterface
   */
  protected $logger;

  /**
   * The drush.console.services service.
   *
   * @var \Drush\Drush
   */
  protected $drush;

  /**
   * Constructs a CmTools object.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file handler.
   * @param \Drupal\Core\Logger\LoggerChannelFactoryInterface $logger
   *   The logger channel factory.
   * @param \Drush\Command\ServiceCommandlist $services
   *   The drush.console.services service.
   */
  public function __construct(Connection $connection, FileSystemInterface $file_system, LoggerChannelFactoryInterface $logger) {
    $this->connection = $connection;
    $this->fileSystem = $file_system;
    $this->logger = $logger;
    $this->drush = Drush::output();
  }

  /**
   * Print variables and messages to the console.
   *
   * @param mixed $var
   *   The variable to output.
   *
   * @return mixed
   *   The value printed to the console.
   */
  public function logToConsole($var) {

    $drush = Drush::output();

    if (gettype($var) != 'string') {
      return $drush->writeln(print_r($var));
    }
    else {
      return $drush->writeln($var);
    }
  }

  /**
   * Log data to a CSV file. Works best for post-row-save logging of HTML tag data.
   *
   * @param array $data
   *   The row of tag information.
   * @param string $file
   *   The complete file path and name of the log file.
   * @param string $message
   *   An optional message to output to the console.
   */
  public function logToFile(array $data, string $file, string $message) {
    $fs = \Drupal::service('file_system');

    // Log the date of this row.
    $data['date'] = date("Y-m-d H:i:s");

    // Ensure the 'migrate-logs' directory exists, create it if not.
    $log_file = $fs->getDestinationFilename($file, FileSystemInterface::EXISTS_ERROR);
    // Try opening the file. Suppress errors until after attempting prepareDirectory.
    //$filestream = @fopen($file, 'a');
    if ($log_file) {
      // Ensure there is a writable directory.
      $dir = $fs->dirname($file);
      if (!$fs->prepareDirectory($dir, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
        throw new MigrateException("Could not create or write to the directory '$dir'");
      }
      // Try again to open the file.
      $filestream = @fopen($file, 'a');

      if (!$filestream) {
        throw new MigrateException("Could not write to file '$file'");
      }
    }

    $logFile = fopen($file, 'a');

    // If the file has no rows, set the headers row with the array keys.
    $f = new \SplFileObject($file, 'r');
    $f->seek(PHP_INT_MAX);
    if ($f->key() <= 0) {
      $headers = array_keys($data);
      fputcsv($logFile, $headers);
    }

    fputcsv($logFile, $data);
    fclose($logFile);

    // Log to the console for verification.
    if (!empty($message)) {
      $this->logToConsole($message);
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
  public function getStorage(string $storage_name = 'citizen_migrate') {
    return \Drupal::service('tempstore.private')->get($storage_name);
  }

  /**
   * Pings the server and returns the response code.
   *
   * @param string $domain
   * @param string $uri
   *
   * @return mixed
   *   Returns 200 integer on success; response_cade and url array on failure.
   */
  public function validateUrl(string $path, string $domain = NULL): mixed {

    if (preg_match('/^\//', $path) && !$domain) {
      throw new MigrateException('$domain needs to be set because $path is not a URL.');
    }

    // Encode the spaces only. urlencode() returns false errors.
    $no_space_path = str_replace(' ', '%20', $path);
    // Ensure a full URL is used.
    $url = !preg_match('/^https?/', $path) ? "https://$domain/$no_space_path" : $no_space_path;

    $headers = get_headers($url);
    // Return an array if the request failed outright.
    if (!$headers) {
      return [
        'response_code' => -1,
        'url' => $url,
      ];
    }

    $response_codes = [];
    if ($headers[0] != 'HTTP/1.1 200 OK') {

      // Search for a 404 value in the array.
      foreach ($headers as $header) {
        preg_match('/HTTP\/1.1 (\d+)/', $header, $http_response_header);
        if (isset($http_response_header[1])) {
          $response_codes[] = $http_response_header[1];
        }
      }

      // Return an array if a 404 was found.
      if (in_array('404', $response_codes)) {
        return [
          'response_code' => 404,
          'url' => $url,
        ];
      }
    }
    return 200;
  }
}
