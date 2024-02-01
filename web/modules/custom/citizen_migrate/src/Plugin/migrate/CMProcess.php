<?php

namespace Drupal\citizen_migrate\Plugin\migrate;

use Drupal\citizen_migrate\Services\CmTools;
use Drupal\migrate\ProcessPluginBase;

abstract class CMProcess extends ProcessPluginBase {

  /**
   * @var \Drupal\citizen_migrate\Services\CmTools
   */
  protected CmTools $cmTools;

  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->cmTools = \Drupal::service('cm.tools');
  }

}
