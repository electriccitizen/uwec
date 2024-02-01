<?php

namespace Drupal\citizen_migrate;

interface TagInterface {

  /**
   * Performs the initial analysis and sets its status property.
   *
   * @return string
   *   A value that indicates the state of the tag to determine next actions.
   */
  public function processTag(): string;

  /**
   * Modifies the tag to be compatible with WYSIWYG editor settings.
   *
   * @return bool
   *   TRUE if the transformation was successful; FALSE on failure.
   */
  public function transformTag(): bool;

  /**
   * Converts the incoming tag to one designated by the parameter.
   *
   * @param string $tagname
   *   The name of the tag: 'a', 'p', 'div', 'h2'.
   *
   * @return \DOMElement|false
   *   The resulting tag after conversion; FALSE on failure.
   */
  public function convertTag(string $tagname): \DOMElement;

  /**
   * Removes the tag from the DOM.
   *
   * @return bool
   *   TRUE if successfully removed from the DOM; FALSE on failure.
   */
  public function removeTag(): bool;

}