<?php

namespace Drupal\citizen_migrate\EventSubscriber;

use Drupal\citizen_migrate\Services\CmTools;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\migrate\Event\MigrateEvents;
use Drupal\migrate\Event\MigrateImportEvent;


class CmPostMigrationLogger implements \Symfony\Component\EventDispatcher\EventSubscriberInterface {

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

  public function __construct(MessengerInterface $messenger, CmTools $cmTools, PrivateTempStoreFactory $storage) {
    $this->messenger = $messenger;
    $this->cmTools = $cmTools;
    $this->storage = $storage;
  }

  /**
   * @inheritDoc
   */
  public static function getSubscribedEvents() {
    $events[MigrateEvents::POST_IMPORT][] = ['storedDataToFile'];
    return $events;
  }

  public function storedDataToFile(MigrateImportEvent $event) {
    $uwecLogs = $this->storage->get('citizen_migrate')->get('uwecLogs');
    // Sort each set of arrays descending by length.
    foreach ($uwecLogs as $row_name => $rows) {
      usort($uwecLogs[$row_name], function($a, $b) {
        return count($b) - count($a);
      });
      array_walk($uwecLogs[$row_name], function ($row) {
        $log_file = isset($row['log_file']) ? "public://migration__logs/{$row['log_file']}" : "public://migration__logs/unprocessed.csv";
        $this->cmTools->logToFile($row, $log_file, '');
      });
    }

  }

}