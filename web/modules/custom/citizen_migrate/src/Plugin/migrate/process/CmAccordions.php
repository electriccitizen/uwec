<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_lookup"
 * )
 */
class CmAccordions extends \Drupal\citizen_migrate\Plugin\migrate\CMProcess {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    /** @var \Drupal\migrate\MigrateLookup $lookup */
    $lookup = \Drupal::service('migrate.lookup');
    $migration = $this->configuration['migration'];

    $accordion_item = $lookup->lookup($migration, [$value[0], $value[1]]);
    return $accordion_item;
  }

}