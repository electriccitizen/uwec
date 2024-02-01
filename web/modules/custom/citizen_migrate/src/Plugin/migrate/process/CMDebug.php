<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\citizen_migrate\CMTraits;
use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_debug"
 * )
 */
class CMDebug extends \Drupal\migrate\ProcessPluginBase {
//  use CMTraits;

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $this->debug($value);
    return $value;
  }


}