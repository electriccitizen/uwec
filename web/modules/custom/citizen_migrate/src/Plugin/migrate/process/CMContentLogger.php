<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_content_logger"
 * )
 */
class CMContentLogger extends CMProcess {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    //$this->cmTools->logToConsole(['value' => $value, 'rowSource' => $row->getSource()]);

//    foreach ($value as $field_name) {
////      $data[$field_name] =
//    }
  }


}