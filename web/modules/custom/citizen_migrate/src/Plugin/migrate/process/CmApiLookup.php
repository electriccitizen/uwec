<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\Core\Site\Settings;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\Row;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GuzzleHttp\ClientInterface;

/**
 * Fetches and searches data from an API with caching.
 *
 * @MigrateProcessPlugin(
 *   id = "cm_api_lookup"
 * )
 */
class CmApiLookup extends ProcessPluginBase implements ContainerFactoryPluginInterface {

  protected $httpClient;

  // Static cache for API responses.
  protected static $cache = [];

  public function __construct(array $configuration, $plugin_id, $plugin_definition, ClientInterface $http_client) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->httpClient = $http_client;
  }

  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client')
    );
  }

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $api_key = Settings::get('uwec_api_key');  //Fetch the API key from the environment variable
    $api_url = "https://athena.apps.uwec.edu/api/suffixes.json";
    $api_url .= "?apikey=$api_key&isactive=1";

    /** @var \Drupal\citizen_migrate\Services\CmTools $cmTools */
    $cmTools = \Drupal::service('cm.tools');

    // Check if the data is already cached.
    if (!isset(self::$cache[$api_url])) {
      $response = $this->httpClient->request('GET', $api_url, [
        'headers' => [
          'Accept' => 'application/json',
//          'Authorization' => 'Bearer ' . $api_key,
        ],
      ]);

      if ($response->getStatusCode() == 200) {
        self::$cache[$api_url] = json_decode($response->getBody(), TRUE);
      } else {
        // Handle non-200 responses or add logging here.
        return NULL;
      }
    }

    $data = self::$cache[$api_url];
    $suffixes = [];
    foreach ($value as $suffix_id) {
      $suffixes[] = $data[$suffix_id]['title'];
//      foreach ($data as $key => $item) {
//        if ($key == $value && $item['title']) { // Assuming you're looking for a specific key in the item.
//          return $item['title']; // Return the 'title' associated with the value.
//        }
//      }
    }
    return implode(' ', $suffixes);


    return NULL; // Return NULL if not found or any issue with API call.
  }
}

