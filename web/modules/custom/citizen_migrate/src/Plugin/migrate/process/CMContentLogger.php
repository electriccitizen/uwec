<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\citizen_migrate\Plugin\migrate\CMProcess;
use Symfony\Component\DomCrawler\Crawler;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_content_logger"
 * )
 */
class CMContentLogger extends CMProcess {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {

    $index = 0;
    $csvData = []; // For logging the content
    $paragraphs = []; // For passing to the next field for processing.

    // The data array to hold the values.
    $csvData['legacy_id'] = $row->getSourceIdValues()['id'];
    $csvData['drupal_id'] = $row->getIdMap()['destid1'] ?? 0;

    // Ensure that $value is a DOMDocument instance.
    if (!$value instanceof \DOMDocument) {
      // Log the value of the video_id and cutline fields.
      if (isset($this->configuration['field'])) {
        $field = $this->configuration['field'];
        $csvData[$field] = $value;
        if ($this->configuration['log_data']) {
          $this->cmTools->logToFile($csvData, "public://source-data/stories_para_$field.csv", '');
        }
        return $field;
      }
      return $value;
    }

    $crawler = new Crawler($value);
    // Select all elements you are interested in. Adjust the XPath as needed.
    $tags = isset($this->configuration['tags']) ? implode(', ', $this->configuration['tags']) : '';
    $nodes = $crawler->filter($tags);

    foreach ($nodes as $node) {
      $csvData['index'] = $index++;
      $csvData['tag'] = $node->nodeName;

      /** @var \DOMElement $domElement */
      $domElement = $node;

      // Use the helper function to remove 'style' and 'class' attributes.
      $this->removeAttributes($domElement, ['style', 'class']);

      // Handle <figure> elements.
      if ($domElement->nodeName == 'figure') {
        unset($csvData['content']);
        $img = $domElement->getElementsByTagName('img')[0];
        $csvData['src'] = $img ? $img->getAttribute('src') : '';
        $csvData['image_id'] = $img ? $img->getAttribute('data-image_id') : '';
        $figcaption = $domElement->getElementsByTagName('figcaption')[0];
        $csvData['caption'] = $figcaption ? $figcaption->textContent : '';
        if ($this->configuration['log_data']) {
          $this->cmTools->logToFile($csvData, "public://source-data/stories_para_images.csv", '');
        }
        $paragraphs[] = $csvData;
        continue;
      }

      // Handle li elements specifically for lists.
      if (in_array($domElement->nodeName, ['ol', 'ul'])) {
        foreach ($domElement->childNodes as $child) {
          if ($child instanceof \DOMElement && $child->tagName === 'li') {
            $this->removeAttributes($child, ['style', 'class']);
          }
        }
        // For ol and ul, save the entire list's HTML.
//        $html = $value->saveHTML($domElement);
//        $csvData['content'] = $html;
      } //else {
        // For non-list elements, save individual HTML.
//        $html = $value->saveHTML($domElement);
        $csvData['content'] = $value->saveHTML($domElement);
//      }
//      // Directly remove attributes from all but li elements, which are handled with their parents
//      if (!in_array($domElement->nodeName, ['ol', 'ul'])) {
//        $domElement->removeAttribute('style');
//        $domElement->removeAttribute('class');
//        // For these, save the individual HTML
//        $html = $value->saveHTML($domElement);
//        $csvData['content'] = $html;
//      } else {
//        // For ol and ul, process and include li children
//        foreach ($domElement->childNodes as $child) {
//          if ($child instanceof \DOMElement && $child->tagName === 'li') {
//            $child->removeAttribute('style');
//            $child->removeAttribute('class');
//          }
//        }
//        // Save the entire list's HTML, including processed li elements
//        $html = $value->saveHTML($domElement);
//        $csvData['content'] = $html;
//      }
      unset($csvData['caption'], $csvData['image_id'], $csvData['src']);
      if ($this->configuration['log_data']) {
        $this->cmTools->logToFile($csvData, "public://source-data/stories_para_text.csv", '');
      }
      $paragraphs[] = $csvData;
    }
    return $paragraphs;
    // Return the original value as this plugin is for logging, not transforming.
//    return $value->saveHTML();
  }

  /**
   * Removes specified attributes from a given DOMElement.
   *
   * @param \DOMElement $element
   *   The DOMElement from which to remove attributes.
   * @param array $attributes
   *   An array of attribute names to remove.
   */
  protected function removeAttributes(\DOMElement $element, array $attributes) {
    foreach ($attributes as $attribute) {
      if ($element->hasAttribute($attribute)) {
        $element->removeAttribute($attribute);
      }
    }
  }



  //  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
//    $tagNames = $this->configuration['tag'];
//    $legacy_id = $row->getSourceIdValues()['id'];
//    $index = 0;
//
//    $csvData = [];
//    $paragraphs = [];
//
//
//    foreach ($tagNames as $tagName) {
//      $tags = $value->getElementsByTagName($tagName);
//      $fileName = '';
//      if ($tags) {
//        foreach ($tags as $t) {
//          //        $i = $index++;
//          $csvData['legacy_id'] = $row->getSourceIdValues()['id'];
//          $csvData['drupal_id'] = $row->getIdMap()['destid1'];
//          $csvData['index'] = $index++;
//          $csvData['tag'] = $tagName;
//
//          if (in_array($tagName, ['p','ol','ul'])) {
//            $fileName = 'p';
//            $csvData['content'] = $value->saveHTML($t);
//          } elseif ($tagName === 'figure') {
//            $fileName = $tagName;
//            $img = $t->getElementsByTagName('img')[0];
//            $csvData['src'] = $img ? $img->getAttribute('src') : '';
//            $csvData['image_id'] = $img ? $img->getAttribute('data-image_id') : '';
//            $figcaption = $t->getElementsByTagName('figcaption')[0];
//            $csvData['caption'] = $figcaption ? $figcaption->textContent : '';
//          }
//          //$this->cmTools->logToConsole($rowData);
//          //        $csvData[] = $rowData;
//          // Write $csvData to a CSV file
//          if ($this->configuration['log_data']) {
////            $this->cmTools->logToConsole($csvData);
//            $this->cmTools->logToFile($csvData, "public://source-data/stories_para_$fileName.csv", '');
//          }
//          $paragraphs[] = $csvData;
//        }
//
//      }
//
//    }
//
//    return $paragraphs;
//
//
////    return $csvData; // Dummy field returns nothing
//  }
//    // Create a new DOMXPath object
//    $xpath = new \DOMXPath($value);
//
//    // Find the <figure> elements
//    $figures = $xpath->query('//figure');
//
//    $gallery_items = [];
//
//    foreach ($figures as $figure) {
//      // Find the <img> within this <figure>
//      $img = $xpath->query('.//img', $figure)->item(0);
//
//      // Get the src and data-image_id attributes from the <img> tag
//      $src = $img->getAttribute('src');
//      $imageId = $img->getAttribute('data-image_id');
//
//      // Find and get the content of the <figcaption>
//      $figcaption = $xpath->query('.//figcaption', $figure)->item(0);
//      $figcaptionText = $figcaption ? $figcaption->textContent : '';
//
//      // Output the results
//      $this->cmTools->logToConsole([
//        'src' => $src,
//        'image_id' => $imageId,
//        'caption' => $figcaptionText
//      ]);
//      $item = [
//        'src' => $src,
//        'image_id' => $imageId,
//        'caption' => $figcaptionText
//      ];
//      $gallery_items[] = $item;
//    }
//    return $gallery_items;
//  }

}