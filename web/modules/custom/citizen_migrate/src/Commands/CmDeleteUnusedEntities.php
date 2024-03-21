<?php

namespace Drupal\citizen_migrate\Commands;

use Drupal\Core\Site\Settings;
use Drush\Commands\DrushCommands;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use GuzzleHttp\Client;
use Drupal\Core\Database\Database;
use Symfony\Component\DependencyInjection\ContainerInterface;

class CmDeleteUnusedEntities extends DrushCommands {
  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new CustomApiIntegrationCommands object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Pulls a list of pages from an API and removes unmatched entities.
   *
   * @command citizen-migrate:delete-unused-entities
   * @aliases cm:due
   * @param string $migration_name The migration name to construct table name.
   * @option entity The entity type to be cleaned: node (default) or paragraph. Use --entity=paragraph to specify.
   * @usage citizen-migrate:delete-unused-entities my_migration
   *   Removes nodes that are not found in the external API list for the specified migration.
   * @usage citizen-migrate:delete-unused-entities my_migration --entity=paragraph
   *   Removes paragraphs that are not found in the external API list for the specified migration.
   */
  public function cleanEntities($migration_name, $options = ['entity' => 'node']) {
    $client = new Client();
    $response = $client->request('GET', 'https://athena.apps.uwec.edu/api/stories.json', [
      'query' => [
        'apikey' => Settings::get('uwec_api_key'),
        'ispublished' => 1,
        'system_id' => 1,
        'migrating_to_drupal' => 1,
        'unit_id' => 35,
        'system_id' => 1
      ]
    ]);
    // content_2col
    // content_3col
    // fauxfile_item
    // fauxfile
    // featured_copy
    // gallery_items
    // gallery
    // links_files
    // stackla
    // standard_rte
    // tabular_rte
    // video
    // page

    if ($response->getStatusCode() == 200) {
      $data = json_decode($response->getBody()->getContents(), TRUE);
      $apiNodeIds = array_column($data, 'id');

      $tableName = "migrate_map_{$migration_name}";
      $connection = Database::getConnection();
      $query = $connection->select($tableName, 'mmp')
        ->fields('mmp', ['sourceid1', 'destid1']);
      $result = $query->execute();

      foreach ($result as $record) {
        if (!in_array($record->sourceid1, $apiNodeIds)) {
          // Load and delete the entity.
          $entityStorage = $this->entityTypeManager->getStorage($options['entity']);
          $entityObject = $record->destid1 ? $entityStorage->load($record->destid1) : NULL;
          if ($entityObject) {
            $entityObject->delete();
            $this->logger()->notice("Deleted {$options['entity']} with ID: " . $record->destid1);
          }
        }
      }
    } else {
      $this->logger()->error("Failed to fetch data from API.");
    }
  }
}