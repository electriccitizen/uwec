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
 *   id = "uwec_url_paragraphs",
 *   source_module = "citizen_migrate"
 * )
 */
class UwecUrlParagraphs extends Url {

  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    // Construct the parameters for the URL.
    $url = $configuration['urls'];

    $configuration['urls'] = $url . '?apikey=' . getenv('API_KEY') . '&limit=' . $configuration['constants']['limit'];
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
  }

  /**
   * Initialize the iterator using the custom data parser.
   *
   * @return DataParserPluginInterface
   */
  public function initializeIterator(): DataParserPluginInterface {
    // Create an instance of the custom data parser
    $parser = new CmJsonDataParser($this->configuration, $this->pluginId, $this->pluginDefinition);

    // Initialize the parser and return it
    return $parser->initializeIterator();
  }


}
