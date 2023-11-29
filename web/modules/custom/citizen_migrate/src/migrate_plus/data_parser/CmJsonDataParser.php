<?php

namespace Drupal\citizen_migrate\migrate_plus\data_parser;

use Drupal\migrate_plus\Annotation\DataParser;
use Drupal\migrate_plus\DataParserPluginBase;
use Drupal\migrate_plus\Plugin\migrate_plus\data_parser\Json;

/**
 * @DataParser(
 *   id = "cm_json_data_parser"
 * )
 */
class CmJsonDataParser extends DataParserPluginBase {

  public array $results = [];

  /**
   * Opens the source URL and prepares the JSON data.
   *
   * @param string $url The URL of the JSON data source.
   * @return bool True if the URL is successfully opened, false otherwise.
   */
  protected function openSourceUrl(string $url): bool {
    try {
      // Attempt to fetch the JSON data from the URL.
      $json = file_get_contents($url);

      // Check if the fetch was successful.
      if ($json === FALSE) {
        // Return false if data fetch failed.
        return FALSE;
      }

      // Decode the JSON data and store it.
      $this->data = json_decode($json, TRUE);

      // Return true indicating successful opening and preparation of the data.
      return TRUE;
    } catch (\Exception $e) {
      // Handle any exceptions that might occur and return false.
      // You could log the exception here if needed.
      return FALSE;
    }
  }

  /**
   * Fetches the next row of data from the source.
   */
  protected function fetchNextRow(): void {
    // Here, iterate over your JSON data and set the current row.
    // Assuming $this->data is your decoded JSON array.
    $this->currentRow = current($this->data);

    // Move the internal pointer of the array forward.
    if ($this->currentRow !== FALSE) {
      next($this->data);
    }
  }

  /**
   * Initialize the iterator.
   *
   * @return \Generator
   */
  public function initializeIterator() {
    /** @var \Drupal\citizen_migrate\Services\CmTools $cmTools */
    $cmTools = \Drupal::service('cm.tools');
    if ($this->openSourceUrl($this->configuration['urls'][0])) {
      foreach ($this->data as $key => $row) {
        // Add the root-level key as 'id' for each row.
        if (!isset($row['id'])) {
          $row['id'] = $key;
        }

        // Depending on the conditions, process each row.
        switch (TRUE) {
          // Gallery Items migration.
          case isset($this->configuration['get_photos']) && $this->configuration['get_photos']:
            foreach ($row['data'] as $dataKey => $para) {
              if (isset($para['gallery']) && is_array($para['gallery']['photos'])) {
                array_walk($para['gallery']['photos'], function ($photo, $i) use (&$para, $row, $dataKey, $cmTools){
                  $photo['val']['parentId'] = $row['id'];
                  $photo['val']['rowIndex'] = $dataKey;
                  $photo['val']['imageIndex'] = $i;
                  $this->results[] = $photo['val'];
                  $cmTools->logToFile($photo['val'], "public://source-logs/{$this->configuration['source_log']}", '');
                }, $this);
              }
            }
            break;

          // Gallery migration.
          case isset($this->configuration['target_entity']) && $this->configuration['target_entity'] == 'gallery':
            foreach ($row['data'] as $dataKey => $para) {
              if (isset($para['gallery']) && is_array($para['gallery']['photos'])) {
                $item_id = [];
                array_walk($para['gallery']['photos'], function ($photo, $i) use (&$para, $row, $dataKey, $cmTools) {
                  $item_id = [];
                  $item_id['id1'] = $row['id'];
                  $item_id['id2'] = $dataKey;
                  $item_id['id3'] = "$i";
                  $para['photos'][] = $item_id;
                }, $this);
                $para['parentId'] = $row['id'];
                $para['rowIndex'] = $dataKey;
                $this->results[] = $para;
              }
            }
            break;

          // Accordion migration.
          case isset($this->configuration['target_entity']) && $this->configuration['target_entity'] == 'accordion':
            foreach ($row['data'] as $dataKey => $para) {
              if (isset($para['accordion'])) {
                $para['parentId'] = $row['id'];
                $para['rowIndex'] = $dataKey;
                $this->results[] = $para;
              }
            }
            break;

          // Links and Files migration.
          case isset($this->configuration['target_entity']) && $this->configuration['target_entity'] == 'utility-bar':
            foreach ($row['data'] as $dataKey => $para) {
              $files = [];
              $links = [];
              if (isset($para['utility-bar']) && is_array($para['utility-bar']['resources'])) {
                array_walk($para['utility-bar']['resources'], function ($resource, $i) use (&$files, &$links) {
//                  $resource['val']['parentId'] = $row['id'];
//                  $resource['val']['rowIndex'] = $dataKey;
//                  $resource['val']['resourceIndex'] = $i;
//                  $resource['val']['rowTitle'] = $para['utility-bar']['title'];
//                  $resource['val']['file'] = $resource['val']['file'] ?? [];
//                  $resource['val']['url'] = $resource['val']['url'] ?? [];
//                  $this->results[] = $resource['val'];
                  if (isset($resource['val']['file'])) {
//                      $files[$i] = $resource['val']['file'];
                    $files[$i] = [
                      'title' => $resource['val']['title'],
                      'fileId' => $resource['val']['file'],
                    ];
                  }
                  if (isset($resource['val']['url'])) {
                    $links[$i] = [
                      'title' => $resource['val']['title'],
                      'url' => $resource['val']['url'],
                    ];
                  }

                }, $this);
                $result['parentId'] = $row['id'];
                $result['rowIndex'] = $dataKey;
                $result['rowTitle'] = $para['utility-bar']['title'];
                $result['files'] = $files;
                $result['links'] = $links;
                $this->results[] = $result;
              }
            }
            break;
          // Page migration.
//          case isset($this->configuration['get_page_rows']) && $this->configuration['get_page_rows']:
//            $migrate_rows = [1, 21, 54, 22, 23, 3, 47, 41, 25, 20, 26, 55, ];
//            foreach ($row['data'] as $dataKey => $para) {
//              if (!in_array($para['row_id'], $migrate_rows)) {
//                continue;
//              }
//
//            }
//            break;

          // Paragraph migrations (generic).
          case isset($row['data']):
            foreach ($row['data'] as $dataKey => $para) {
              // Ignore rows that don't match the configured row_id value.
              if (isset($this->configuration['row_id']) && $para['row_id'] != $this->configuration['row_id']) {
                continue;
              }
              // For gallery, bring the photos array to the top level.
//              if (
//                isset($this->configuration['get_photos']) &&
//                $this->configuration['get_photos'] &&
//                is_array($para['gallery']['photos'])
//              ) {
//                array_walk($para['gallery']['photos'], function ($photo, $i) use (&$para){
//                  $para['photos'][$i] = $photo['val']['image_id'];
//                });
//              }
//              print_r($para);
              $para['parentId'] = $row['id'];
              $para['rowIndex'] = $dataKey;
              $this->results[] = $para;
            }
            break;
          case isset($row['schedules']):
            foreach ($row['schedules'] as $schedule) {
              $schedule['parentId'] = $row['id'];
              $schedule['parentName'] = $row['name'];
              $schedule['parentLocationId'] = $row['location_id'];
              $this->results[] = $schedule;
            }
            break;
          default:
            $this->results[$key] = $row;
        }
      }
    }
//    print_r($this->results);
    reset($this->results); // Reset the internal pointer of the results array
    return $this;
  }
//  public function initializeIterator() {
////    $this->results = [];
////print_r($this->configuration);
//    if ($this->openSourceUrl($this->configuration['urls'][0])) {
//      // Process each row of data
//      while ($this->data && ($row = current($this->data))) {
//        $this->fetchNextRow();
//
////        if (isset($this->configuration['add_root_keys_to_fields']) && $this->configuration['add_root_keys_to_fields']) {
////          $key = key($this->data); // Get the current key
////          $row['id'] = $key;       // Add the key as 'id'
////          print_r($row);
////          $this->results[] = $row;
////        }
//        switch (TRUE) {
//          case !isset($row['id']):
//            print_r(['key' => key(current($this->data)), 'title' => $row['title']]);
//            break;
//          // Import Row paragraphs.
//          case isset($row['data']):
////            $migrate_rows = [1, 21, 54, 22, 23, 3, 47, 41, 25, 20, 26, 55, ];// 8, 18, 13
//            foreach($row['data'] as $key => $para) {
////              if (!in_array($para['row_id'], $migrate_rows)) {
//              if ($para['row_id'] != $this->configuration['row_id']) {
//                continue;
//              }
//              $para['parentId'] = $row['id'];
//              $para['rowIndex'] = $key;
////              print_r($para);
////              $para['data'] = $row['data'][$key];
//              $this->results[] = $para;
//            }
//            break;
//
//          // Import Hour Sets paragraphs.
//          case isset($row['schedules']):
//            foreach ($row['schedules'] as $schedule) {
//              $schedule['parentId'] = $row['id'];
//              $schedule['parentName'] = $row['name'];
//              $schedule['parentLocationId'] = $row['location_id'];
//              $this->results[] = $schedule;
//            }
//            break;
//        }
//        next($this->data); // Move to the next row
//      }
//    }
//    reset($this->results); // Reset the internal pointer of the results array
//    return $this;
//  }

  public function current() {
    return current($this->results);
  }

  public function next() {
    next($this->results);
  }

  public function key() {
    return key($this->results);
  }

  public function valid() {
    return key($this->results) !== null;
  }

  public function rewind() {
    reset($this->results);
  }
}