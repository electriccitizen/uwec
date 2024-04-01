<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_merge_arrays"
 * )
 */
class CmMergeArrays extends \Drupal\citizen_migrate\Plugin\migrate\CMProcess {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $layout_paragraph_id = [
      'legacy_id' => $value[1],
      'drupal_id' => 0,
      'index' => $value[1],
      'tag' => "$value[1]",
    ];
    return array_merge($value[0], [$layout_paragraph_id]);
  }


}