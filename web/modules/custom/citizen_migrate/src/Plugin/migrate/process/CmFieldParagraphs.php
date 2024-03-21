<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_field_paragraphs"
 * )
 */
class CmFieldParagraphs extends \Drupal\citizen_migrate\Plugin\migrate\CMProcess {

  protected array $migrate_rows = [
    '1' => 'standard_rte',
//    '3' => 'accordion',
    '20' => 'links_files',
    '21' => 'tabular_rte',
    '22' => 'content_2col',
    '23' => 'content_3col',
    '25' => 'stackla',
    '26' => 'video',
//    '41' => 'social_icons',
    '47' => 'gallery',
    '54' => 'featured_copy',
    '55' => 'fauxfile',
  ];

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    /** @var \Drupal\migrate\MigrateLookup $lookup */
    $lookup = \Drupal::service('migrate.lookup');
    $rows = [];

//    $migrate_rows = [1, 21, 54, 22, 23, 47, 41, 25, 20, 26, 55, ]; // Rows not migrating: 3,
    foreach ($value[0] as $key => $data_row) {
      if (!in_array($data_row['row_id'], array_keys($this->migrate_rows))) {
        continue;
      }
      $para_id = $lookup->lookup(
        $this->migrate_rows[$data_row['row_id']],
        [$row->getSourceProperty('id'), $key],
      );
//      $data_row_ids['id'] = $row->getSourceProperty('id');
//      $data_row_ids['row_key'] = $key;
//      $data_row_ids['row_type'] = $this->migrate_rows[$data_row['row_id']];
//      $rows[$key] = $data_row_ids;
      $rows[] = isset($para_id[0]) ? $para_id[0] : [];
    }
//    $this->cmTools->logToConsole($rows);
    // Lookup the layout_paragraph entity.
    $layout_id = $lookup->lookup('layout_pages', [$value[1]]);
    $rows[] = $layout_id[0];
    return $rows;
  }

}