<?php

namespace Drupal\citizen_migrate\Plugin\migrate\source;

use Drupal\citizen_migrate\migrate_plus\data_parser\CmJsonDataParser;
use Drupal\migrate\Annotation\MigrateSource;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate_plus\DataParserPluginInterface;
use Drupal\migrate_plus\Plugin\migrate\source\Url;
use Drush\Drush;

/**
 * Custom URL plugin for UWEC migrations; adds the API key parameter to the url.
 *
 * @MigrateSource(
 *   id = "uwec_url",
 *   source_module = "citizen_migrate"
 * )
 */
class UwecUrl extends Url {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    // Construct the parameters for the URL.
    $url = $configuration['urls'];

    $configuration['urls'] = $url . '?apikey=' . getenv('API_KEY') . '&limit=' . $configuration['constants']['limit'];
    $configuration['urls'] = $configuration['urls'] . '&unit_id=' . $configuration['constants']['unit_id'];
    $configuration['urls'] = $configuration['urls'] . '&isactive=' . $configuration['constants']['isactive'];
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

  protected function initializeIterator(): DataParserPluginInterface {
    // Get the original iterator from the parent class
    $iterator = parent::initializeIterator();

    if (
      isset($this->configuration['add_root_keys_to_fields']) &&
      $this->configuration['add_root_keys_to_fields']
    ) {
      $parser = new CmJsonDataParser($this->configuration, $this->pluginId, $this->pluginDefinition);
      return $parser->initializeIterator();
    }
    return $iterator;
  }
}
