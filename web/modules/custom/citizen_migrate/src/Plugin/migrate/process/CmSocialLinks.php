<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_social_links"
 * )
 */
class CmSocialLinks extends \Drupal\citizen_migrate\Plugin\migrate\CMProcess {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    if (is_array($value)) {
      $platforms = [];
      foreach($value as $platform => $uri) {
        $platforms[] = [
          'platform' => $platform,
          'uri' => $uri,
        ];
      }
      return $platforms;
    }
    return [];
  }

}