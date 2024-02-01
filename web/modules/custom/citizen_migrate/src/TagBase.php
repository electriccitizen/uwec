<?php

namespace Drupal\citizen_migrate;

use Drupal\migrate\Row;

abstract class TagBase implements TagInterface {

  /**
   * The document object to be processed.
   *
   * @var \DOMDocument
   */
  protected $doc;

  /**
   * The plugin configuration.
   *
   * @var array
   */
  protected $config;

  /**
   * The tag node being processed.
   *
   * @var \DOMElement
   */
  protected $tag;

  /**
   * The row of data from the migration source.
   *
   * @var \Drupal\migrate\Row
   */
  protected $row;

  /**
   * Information about this tag.
   *
   * @var array
   */
  protected $data = [];

  /**
   * The array of file names to log various results to.
   *
   * @var array
   */
  protected $logs;

  /**
   * The process log directory path.
   *
   * @var string
   */
  protected $logDir;

  /**
   * The state of this tag. Used to determine additional processing.
   *
   * @var string
   */
  protected $status;

  /**
   * Construct a tag instance.
   *
   * @param \DOMElement $tag
   *   The node to be processed.
   * @param \Drupal\migrate\Row $row
   *   The row of data from the migration source.
   * @param array $config
   *   The configuration passed by the plugin.
   *   The directory path where log files are saved.
   */
  public function __construct(\DOMElement &$tag, Row $row, array $config, \DOMDocument $doc) {
    $this->tag = $tag;
    $this->row = $row;
    $this->config = $config;
    $this->doc = $doc;
    $this->logDir = $config['log_dir'];
    $this->data['mig_name'] = '';
    $this->data['d7_nid'] = '';
    $this->data['d9_nid'] = '';
    $this->data['node_title'] = '';
    $this->data['site_section'] = '';
    $this->data['tag'] = $tag->nodeName;
  }

  /**
   * Create a static tag instance.
   *
   * @param \DOMElement $tag
   *   The tag to be processed.
   * @param \Drupal\migrate\Row $row
   *   The row of data from the migration source.
   * @param array $config
   *   The configuration passed by the plugin.
   *
   * @return static
   */
  public static function create(\DOMElement $tag, Row $row, array $config, \DOMDocument $doc): object {
    return new static($tag, $row, $config, $doc);
  }

  /**
   * @inheritdoc
   */
  public function convertTag(string $tagname): \DOMElement {
    $new_tag = $this->doc->createElement($tagname);
    return $this->tag->parentNode->replaceChild($new_tag, $this->tag);
  }

  /**
   * @inheritdoc
   */
  public function processTag(): string {
    $this->status = 'undetermined';
    return $this->status;
  }

  /**
   * @inheritdoc
   */
  public function removeTag(): bool {
    return $this->tag->parentNode->removeChild($this->tag);
  }

  /**
   * @inheritdoc
   */
  public function transformTag(): bool {
    return FALSE;
  }

  /**
   * Returns the $data array for this tag.
   *
   * @return array
   *   All the data collected about this tag when this function is called.
   */
  public function getTagData(): array {
    return $this->data;
  }

  /**
   * Returns the status of this tag.
   *
   * @return string
   *   The status used to determine further processing.
   */
  public function getStatus(): string {
    return $this->status;
  }

}