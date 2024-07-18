<?php

declare(strict_types=1);

namespace Drupal\citizen_menu_info;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Menu\DefaultMenuLinkTreeManipulators;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Routing\RouteMatchInterface;

/**
 * @todo Add class description.
 */
final class MenuInfo {

  /**
   * Constructs a MenuInfo object.
   */
  public function __construct(
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly MenuLinkTreeInterface $menuLinkTree,
    private readonly DefaultMenuLinkTreeManipulators $menuDefaultTreeManipulators,
    private readonly RouteMatchInterface $routeMatch,
  ) {}

  public function getParentNode($node) {
    // Load menu links associated with the node.
    $menu_links = $this->entityTypeManager->getStorage('menu_link_content')->loadByProperties([
      'link__uri' => 'entity:node/' . $node->id(),
    ]);

    if (empty($menu_links)) {
      return NULL;
    }

    // Extract the first element.
    $menu_link = reset($menu_links);
    $parent_id = $menu_link->getParentId();

    if (!$parent_id) {
      return NULL;
    }

    // Extract UUID from the parent_id.
    $parent_uuid = str_replace('menu_link_content:', '', $parent_id);
    $parent_links = $this->entityTypeManager->getStorage('menu_link_content')->loadByProperties(['uuid' => $parent_uuid]);
    $parent_link = reset($parent_links);

    if (!$parent_link) {
      return NULL;
    }

    // Extract the node ID from the parent link's URI and load the node.
    $parent_node_id = $this->extractNodeIdFromUri($parent_link->getUrlObject()->getInternalPath());
    return $parent_node_id ? $this->entityTypeManager->getStorage('node')->load($parent_node_id) : NULL;
  }

  public function getDepartmentNode($node) {
    while ($node) {
      $parent_node = $this->getParentNode($node);
      if ($parent_node && $parent_node->getType() === 'department') {
        return $parent_node;
      }
      $node = $parent_node;
    }
    return NULL;
  }

  protected function extractNodeIdFromUri($uri) {
    if (preg_match('/node\/(\d+)/', $uri, $matches)) {
      return $matches[1];
    }
    return NULL;
  }
}
