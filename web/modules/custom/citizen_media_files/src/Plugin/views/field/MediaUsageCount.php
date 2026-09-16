<?php

namespace Drupal\citizen_media_files\Plugin\views\field;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\entity_usage\EntityUsageInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Usage count for a media row, computed via the entity_usage service.
 *
 * Deliberately a non-SQL field: it adds no relationship, no join, and no
 * aggregation to the host view, so it cannot break an existing view's query
 * the way retrofitting views aggregation can (a group_by flipped on an
 * arbitrary media view produced malformed SQL on ec2025).
 *
 * @ViewsField("citizen_media_files_usage_count")
 */
class MediaUsageCount extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity usage service.
   *
   * @var \Drupal\entity_usage\EntityUsageInterface
   */
  protected $entityUsage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityUsage = $container->get('entity_usage.usage');
    return $instance;
  }

  /**
   * Constructs the plugin.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, ?EntityUsageInterface $entity_usage = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if ($entity_usage) {
      $this->entityUsage = $entity_usage;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function query() {
    // Non-SQL field: nothing to add to the view query.
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $this->getEntity($values);
    if (!$entity) {
      return '';
    }
    $count = 0;
    foreach ($this->entityUsage->listSources($entity) as $sources) {
      foreach ($sources as $records) {
        $count += is_array($records) ? count($records) : 1;
      }
    }
    $url = Url::fromRoute('entity_usage.usage_list', [
      'entity_type' => $entity->getEntityTypeId(),
      'entity_id' => $entity->id(),
    ]);
    return [
      '#type' => 'inline_template',
      '#template' => '<div>{{ count }}</div><a href="{{ url }}" target="_blank">{% trans %}View Usage{% endtrans %}</a>',
      '#context' => [
        'count' => $count,
        'url' => $url->toString(),
      ],
    ];
  }

}
