<?php

namespace Drupal\citizen_custom\Services;

use Drupal\node\Entity\Node;
use Drupal\taxonomy\Entity\Term;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Provides functionality related to Job content.
 */
class CitizenOfficeFilter {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new CitizenJobService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Gets distinct Office terms referenced by published Job nodes.
   *
   * @return \Drupal\taxonomy\Entity\Term[]
   *   An associative array of Term entities keyed by tid.
   */
  public function getReferencedOffices(): array {
    $tids = [];

    $nids = \Drupal::entityQuery('node')
      ->condition('type', 'job')
//      ->condition('status', 1)
//      ->exists('field_office_term')
      ->accessCheck(TRUE)
      ->execute();

    if (empty($nids)) {
      return [];
    }

    $nodes = $this->entityTypeManager->getStorage('node')->loadMultiple($nids);

    foreach ($nodes as $node) {
      /** @var \Drupal\node\Entity\Node $node */
      foreach ($node->get('field_office_term')->referencedEntities() as $term) {
        $tids[$term->id()] = $term;
      }
    }

    return $tids;
  }

}