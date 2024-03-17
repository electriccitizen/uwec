<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_layout_section"
 * )
 */
class CmLayoutSection extends \Drupal\citizen_migrate\Plugin\migrate\CMProcess {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $query = \Drupal::database()->select('paragraphs_item_field_data', 't')
      ->fields('t', ['id','revision_id'])
      ->condition('t.parent_id', $value);
    $result = $query->execute();
    $results = [];

    foreach ($result as $record) {
      $results[] = [
        'id' => $record->id,
        'revision_id' => $record->revision_id,
      ];
    }
    return $results;
  }


}