<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\citizen_migrate\Plugin\migrate\CMProcess;
use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "citizen_behavior_settings"
 * )
 */
class CitizenBehaviorSettings extends CMProcess {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $behavior_settings = [];
    // If no source value is provided, return the layout paragraph settings.
    if (!$value) {
      $behavior_settings = [
        'layout_paragraphs' => [
          'layout' => 'layout_onecol',
          'config' => ['label' => ''],
          'parent_uuid' => NULL,
          'region' => NULL,
        ]
      ];
    }
    else {
      // When a source value is provided, return child paragraph settings.
      $uuid = $this->getUuid($value);
      $behavior_settings = [
        'layout_paragraphs' => [
          'parent_uuid' => $uuid,
          'region' => 'content'
        ]
      ];
    }
    return serialize($behavior_settings);
  }

  protected function getUuid(array $id) {
    $query = \Drupal::database()->select('paragraphs_item', 't')
      ->fields('t', ['uuid'])
      ->condition('t.id', $id[0])
      ->condition('t.revision_id', $id[1]);
    return $query->execute()->fetchField();
  }
}