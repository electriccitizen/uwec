<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_array_merge"
 * )
 */
class CmArrayMerge extends \Drupal\citizen_migrate\Plugin\migrate\CMProcess {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (is_array($value) && is_array($value[0]) && is_array($value[1])) {
      return array_merge($value[0], $value[1]);
    }
  }

}