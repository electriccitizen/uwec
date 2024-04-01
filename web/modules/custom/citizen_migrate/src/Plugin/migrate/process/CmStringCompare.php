<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\citizen_migrate\Plugin\migrate\CMProcess;
use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_string_compare"
 * )
 */
class CmStringCompare extends CMProcess {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    return $value[0] === $value[1];
  }


}