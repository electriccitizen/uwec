<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_profile_links"
 * )
 */
class CmProfileLinks extends \Drupal\citizen_migrate\Plugin\migrate\CMProcess {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $link_titles = [
      'Website',
      'People Page',
      'Google Scholar',
      'CV/Resume',
      'Resume',
      'CV',
    ];
    $links = [];
    foreach ($value as $key => $val) {
      if (empty($val)) {
        continue;
      }
      $links[] = [
        'title' => $link_titles[$key],
        'uri' => $val,
      ];
    }
    return $links;
  }

}