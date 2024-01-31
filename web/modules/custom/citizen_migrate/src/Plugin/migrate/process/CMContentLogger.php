<?php

namespace Drupal\citizen_migrate\Plugin\migrate\process;

use Drupal\migrate\Annotation\MigrateProcessPlugin;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;
use Drupal\citizen_migrate\Plugin\migrate\CMProcess;

/**
 * @MigrateProcessPlugin(
 *   id = "cm_content_logger"
 * )
 */
class CMContentLogger extends CMProcess {

  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $tag = $this->configuration['tag'];
    $legacy_id = $row->getSourceIdValues()['id'];
    $index = 0;

    $csvData = [];
    $paragraphs = [];


//    foreach (['p', 'figure'] as $tagName) {
      $tags = $value->getElementsByTagName($tag);
      if ($tags) {
        foreach ($tags as $t) {
          //        $i = $index++;
          $csvData['legacy_id'] = $row->getSourceIdValues()['id'];
          $csvData['index'] = $index++;
          $csvData['tag'] = $tag;

          if ($tag === 'p') {
            $csvData['content'] = $value->saveHTML($t);
          } elseif ($tag === 'figure') {
            $img = $t->getElementsByTagName('img')[0];
            $csvData['src'] = $img ? $img->getAttribute('src') : '';
            $csvData['image_id'] = $img ? $img->getAttribute('data-image_id') : '';
            $figcaption = $t->getElementsByTagName('figcaption')[0];
            $csvData['caption'] = $figcaption ? $figcaption->textContent : '';
          }
          //$this->cmTools->logToConsole($rowData);
          //        $csvData[] = $rowData;
          // Write $csvData to a CSV file
          if ($this->configuration['log_data']) {
            $this->cmTools->logToFile($csvData, "public://source-data/stories_para_$tag.csv", '');
          }
          $paragraphs[] = $csvData;
        }
        return $paragraphs;
      }

//    }




    return $csvData; // Dummy field returns nothing
  }
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