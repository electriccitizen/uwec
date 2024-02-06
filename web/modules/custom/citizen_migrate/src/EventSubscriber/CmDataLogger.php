<?php

namespace Drupal\citizen_migrate\EventSubscriber;

use Drupal\citizen_migrate\Services\CmTools;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateMapSaveEvent;
use Drupal\migrate\Event\MigratePostRowSaveEvent;
use Drupal\migrate\Row;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Citizen Migrate event subscriber.
 */
class CmDataLogger implements EventSubscriberInterface {

  /**
   * The messenger.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The storage from which we will get the tag data for logging.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected $storage;

  /**
   * The Citizen Migrate Tools service.
   *
   * @var \Drupal\citizen_migrate\Services\CmTools
   */
  protected $cmTools;

  /**
   * Holds the migration configuration data.
   *
   * @var \Drupal\migrate\Plugin\MigrationInterface
   */
  protected $migration;

  /**
   * Holds the complete data set for each row.
   *
   * @var \Drupal\migrate\Row
   */
  protected $row;

  /**
   * @inheritDoc
   */

  /**
   * Constructs event subscriber.
   *
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger.
   */
  public function __construct(MessengerInterface $messenger, CmTools $cmTools, PrivateTempStoreFactory $storage) {
    $this->messenger = $messenger;
    $this->cmTools = $cmTools;
    $this->storage = $storage;
  }

  public static function getSubscribedEvents() {
    $events = [];
    $events[MigrateEvents::POST_ROW_SAVE] = ['setMigrationData'];
    $events[MigrateEvents::MAP_SAVE] = ['logData'];
    return $events;
  }

  /**
   * Set the migration and row properties once the row has been saved.
   *
   * @param \Drupal\migrate\Event\MigratePostRowSaveEvent $event
   *
   * @return void
   * @throws \Drupal\migrate\MigrateException
   */
  public function setMigrationData (MigratePostRowSaveEvent $event)  {
    // Set the migration and row properties.
    $this->migration = $event->getMigration();
    $this->row = $event->getRow();
  }

  /**
   * With the migrate_map data saved, log the migration data to CSV.
   *
   * @param \Drupal\migrate\Event\MigrateMapSaveEvent $event
   *
   * @return void
   * @throws \Drupal\migrate\MigrateException
   */
  public function logData(MigrateMapSaveEvent $event) {
    $map = $event->getFields();

    // Get the log_data value from the source configuration.
    if ($this->migration) {
      $source_config = $this->migration->getSourceConfiguration();
      $log_data = $source_config['log_data'] ?? FALSE;

      if ($log_data) {
        // Log tag processing data.
        $this->logTagData($map);
        // Log node migration data.
        $this->logRowData($map);
        // Log UWEC row data.
//        $this->logUwecRowData($map);
      }
    }
  }

  /**
   * Iteratively collapses arrays into $key: $value strings.
   * $value strings have a max-length of 50 characters.
   *
   * @param array $array
   * @param string $string
   *
   * @return string
   */
  public function arrayKeyValToString(array $array, string &$string = ''): string {
    foreach ($array as $key => $value) {
      if (is_array($value) && count($value) > 0) {
        $this->arrayKeyValToString($array[$key], $string);
      }
      else {
        // Return only the first 50 characters of the value.
        $value = is_string($value) ? substr($value, 0, 500) : $value;
        $string .= empty($value) ? "$key: null" : "$key: $value";
        $string .= ' | ';
      }
    }
    // Return the value but remove the trailing ' | '.
    return substr($string, 0, -3);
  }

  /**
   * Log node-migration data to a file.
   *
   * @param array $map
   *
   * @return void
   * @throws \Drupal\migrate\MigrateException
   */
  public function logRowData(array $map) {
    // Get the objects containing the data.
    $source_config = $this->migration->getSourceConfiguration();
    $source = $this->row->getSource();
    $dest_config = $this->migration->getDestinationConfiguration();
    $destination = $this->row->getDestination();

    // Create the data array for this row.
    $data['src_bundle'] = $source['type'] ?? '';
    $data['src_id'] = $map['sourceid1'] ?? '';
    $data['dst_bundle'] = $this->migration->getDestinationConfiguration()['default_bundle'] ?? '';
    $data['dst_id'] = $map['destid1'] ?? '';
    $data['title'] = $source['title'] ?? '';

    // Iterate over each field and add to the data array.
    foreach ($destination as $key => $value) {
      if (empty($value)) {
        $data[$key] = 'null';
        continue;
      }
      $data[$key] = is_array($value) ? $this->arrayKeyValToString($value) : $value;
    }

    // Log the data array to a file named for the content type.
    if (empty($data['dst_bundle'])) {
      $data['dst_bundle'] = 'file';
    }
//    $this->cmTools->logToConsole($data['dst_bundle']);
    $log_file = "public://migration_logs/{$data['dst_bundle']}_data.csv";
    $this->cmTools->logToFile($data, $log_file, '') ;

  }

  /**
   * Log data from tag processing/transformation to file.
   *
   * @param array $map
   *
   * @return void
   * @throws \Drupal\migrate\MigrateException
   */
  public function logTagData(array $map) {
    // Get the objects containing migration data.
    $source = $this->row->getSource();

    // Get the temporary data store.
    $storage = $this->storage->get('citizen_migrate');
    // Get the array of row tags data.
    $row_tags = $storage->get('row_tags');

    if ($row_tags) {

      foreach ($row_tags as $tag_data) {
        // Update any placeholders.
        //*MTLawHelp        $tag_data['d9_nid'] = $map['destid1'] ?? '';
        $tag_data['node_title'] = $source['title'] ?? '';

        // Retrieve these values to use in the log function.
        $log_message = $storage->get('log_to_console') ?
          "{$tag_data['d7_nid']}/{$tag_data['d9_nid']} {$tag_data['tag']} {$tag_data['status']}: {$tag_data['error']}" : '';
        $log_file = !empty($tag_data['log_file']) ? "{$storage->get('log_dir')}/{$tag_data['log_file']}" : NULL;

        // Remove info we don't need to log to the file.
        unset($tag_data['log_file']);
        unset($tag_data['log_message']);

        // Log the data.
        if ($log_file) {
          $this->cmTools->logToFile(
            $tag_data,
            $log_file,
            $log_message
          );
        }
      }
      // Clear the data to prevent values persisting to the next row.
      $storage->delete('row_tags');
    }

  }

  public function logUwecRowData(array $map) {
    // Return early if no destination ID is available.
    if (!isset($map['destid1'])) {
      return;
    }
//    $this->cmTools->logToConsole($map);
    /** @var \Drupal\Core\TempStore\PrivateTempStore $cm_storage */
    $cm_storage = $this->storage->get('citizen_migrate');
    $uwec_row_data = $cm_storage->get('uwec_row_data');

    $uwecLogs = $cm_storage->get('uwecLogs') ?? [];

    foreach ($uwec_row_data as $key => $rows) {
      array_walk($rows, function(&$row,$index) use ($map, $key, &$uwecLogs){
        $row['src_id'] = $map['sourceid1'] ?? 'n/a';
        $row['des_id'] = $map['destid1'] ?? 'n/a';
        $uwecLogs[$key][] = $row;
      });

//      $this->cmTools->logToConsole(['key' => $key, 'row' => $row]);
//      unset($row['log_file']);

//      $log_file = "public://migration-logs/para_{$row['row_name']}_data.csv";
//      $this->cmTools->logToFile($row, $log_file, '');
    }
//    $this->cmTools->logToConsole($uwecLogs);
    $cm_storage->set('uwecLogs', $uwecLogs);
  }

}
