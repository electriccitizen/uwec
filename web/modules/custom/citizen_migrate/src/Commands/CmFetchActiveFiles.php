<?php

namespace Drupal\citizen_migrate\Commands;

use Drupal\Core\File\FileSystemInterface;
use Drush\Commands\DrushCommands;
use Drupal\Core\Logger\RfcLogLevel;
use GuzzleHttp\Exception\RequestException;

class CmFetchActiveFiles extends DrushCommands {

  protected array $image_ids = [
    'endpoint' => 'n/a',
    'ent_id' => 'n/a',
    'row_id' => 'n/a',
    'rowIndex' => 'n/a',
    'image_id' => 'n/a',
//    'para_weight' => 'n/a',
//    'markup' => 'n/a',
  ];

  protected array $doc_links = [
    'endpoint' => 'n/a',
    'ent_id' => 'n/a',
    'row_id' => 'n/a',
    'rowIndex' => 'n/a',
    'docUrl' => 'n/a',
  ];

  protected array $vid_links = [
    'endpoint' => 'n/a',
    'ent_id' => 'n/a',
    'row_id' => 'n/a',
    'rowIndex' => 'n/a',
    'vidUrl' => 'n/a',
    'title' => 'n/a',
  ];

  protected string $outputFile = '';

  protected int $images_from_text = 0;

  /**
   * Runs the API data fetch and processing.
   *
   * @command citizen-migrate:fetch-active-files
   * @aliases cm-faf
   * @usage citizen-migrate:fetch-active-files
   *   Fetch image ids from the Athena API and log them to CSV.
   */
  public function fetchData() {
    // Your script logic here.
    $this->output()->writeln("Fetching and processing data...");
    $baseURL = 'https://athena.apps.uwec.edu/api';
    $apiKey = getenv('API_KEY');
    $endpoints = ['pages.json', 'profiles.json', 'programs.json', 'stories.json', 'testimonials.json'];
    $this->outputFile = "public://source-data/active_image_ids.csv";

    /** @var \Drupal\citizen_migrate\Services\CmTools $cmTools */
    $cmTools = \Drupal::service('cm.tools');

    foreach ($endpoints as $endpoint) {
      $url = "$baseURL/$endpoint?apikey=$apiKey&isactive=1&ispublished=1";

      // Further filter Story nodes to those from unit_id 35.
      if ($endpoint === 'stories.json') {
        $url .= '&system_id=1&unit_id=35';
      }
      if ($endpoint === 'pages.json') {
        $url .= '&system_id=1&migrating_to_drupal=1';
      }

      $data = $this->fetch_data($url);
      $this->image_ids['rowIndex'] = 'n/a';
      $this->doc_links['rowIndex'] = 'n/a';
      $this->vid_links['rowIndex'] = 'n/a';

      if (is_array($data) && !empty($data)) {
        $this->output()->writeln("Precessing $endpoint ...");
        $this->image_ids['endpoint'] = $endpoint;
        $this->doc_links['endpoint'] = $endpoint;
        $this->vid_links['endpoint'] = $endpoint;

        foreach ($data as $item) {
          $this->image_ids['ent_id'] = $item['id'];
          $this->image_ids['rowIndex'] = 'n/a';
          $this->doc_links['ent_id'] = $item['id'];
          $this->doc_links['rowIndex'] = 'n/a';
          $this->vid_links['ent_id'] = $item['id'];
          $this->vid_links['rowIndex'] = 'n/a';

          // Write the appropriate field values to the CSV file.
          switch ($endpoint) {
            case 'pages.json': // Adjusted to .json
              // Log the value of the image_id and the banner/image_id fields.
              if (isset($item['banner']['image_id']) && $item['banner']['image_id'] > 0) {
                $this->logImageData($item['banner']['image_id'], $this->image_ids, $this->outputFile);
              }
              if (isset($item['image_id']) && $item['image_id'] > 0) {
                $this->logImageData($item['image_id'], $this->image_ids, $this->outputFile);
              }
              // Iterate through the text rows to log image_ids embedded in text content.
              $this->processRows($item['data']);
              break;

            case 'profiles.json': // Adjusted to .json
              if (isset($item['headshot_image_id']) && $item['headshot_image_id'] > 0) {
                $this->logImageData($item['headshot_image_id'], $this->image_ids, $this->outputFile);
              }
              break;

            default:
              if (isset($item['image_id']) && $item['image_id'] > 0) {
                $this->logImageData($item['image_id'], $this->image_ids, $this->outputFile);
              }
              if (isset($item['article'])) {
                $this->logImageIdsInHtml($item['article'], 0);
              }
              break;
          }
        }
      }
    }
    $this->removeDuplicates($this->outputFile);
    $this->removeDuplicates('public://source-data/doc_links.csv');
    $this->removeDuplicates('public://source-data/vid_links.csv');
    $this->output()->writeln("Images found in text: $this->images_from_text");
  }

  /**
   * Fetches JSON data from the API endpoint.
   *
   * @param $url
   *
   * @return mixed|null
   * @throws \GuzzleHttp\Exception\GuzzleException
   */
  function fetch_data($url) {
    try {
      // Get the HTTP client service
      $client = \Drupal::httpClient();

      // Send a GET request
      $response = $client->get($url, [
        'headers' => [
          'Accept' => 'application/json',
        ],
      ]);

      // Decode JSON response
      return json_decode($response->getBody(), TRUE);
    }
    catch (RequestException $e) {
      // Handle exception or log error
      \Drupal::logger('citizen_migrate')->log(RfcLogLevel::ERROR, 'Request failed: @message', ['@message' => $e->getMessage()]);
      return NULL;
    }
  }

  /**
   * Gathers additional image data from the API and logs it to a CSV file.
   *
   * @param $image_id
   * @param $image_data
   * @param $output_file
   *
   * @return bool
   * @throws \Drupal\migrate\MigrateException
   */
  function logImageData($image_id, $image_data, $output_file) {
    $baseURL = 'https://athena.apps.uwec.edu/api/images.json';
    $apiKey = getenv('API_KEY');
    $url = "$baseURL?apikey=$apiKey&id=$image_id";

    /** @var \Drupal\citizen_migrate\Services\CmTools $cmTools */
    $cmTools = \Drupal::service('cm.tools');

    $data = $this->fetch_data($url);
    if (is_array($data) && !empty($data)) {
      $image_data['image_id'] = $image_id;
      $image_data['alt'] = $data[$image_id]['alt'];
      $image_data['title'] = $data[$image_id]['title'];
      $image_data['url'] = $data[$image_id]['url'];
      $image_data['updated_at'] = $data[$image_id]['updated_at'];

      $cmTools->logToFile($image_data, $output_file, '');
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Removes duplicate rows from a CSV file based on 'image_id'.
   *
   * @param string $filePath Path to the CSV file.
   */
  public function removeDuplicates(string $filePath) {
    $this->output()->writeln("Removing duplicates from $filePath...");

    // Check if file exists
    if (!file_exists($filePath) || !is_readable($filePath)) {
      $this->output()->writeln("File not found or not readable: $filePath");
      return;
    }

    $header = [];
    $data = [];
    $unique = [];

    // Read the CSV file
    if (($handle = fopen($filePath, 'r')) !== FALSE) {
      while (($row = fgetcsv($handle)) !== FALSE) {
        if (empty($header)) {
          $header = $row;
          continue;
        }
        $data[] = $row;
      }
      fclose($handle);
    }

    // Filter out duplicates
    foreach ($data as $line) {
      // Assuming 'image_id' is in the third column
      $imageId = $line[4];
      if (!isset($unique[$imageId])) {
        $unique[$imageId] = $line;
      }
    }

    // Write unique data back to the file
    if (($handle = fopen($filePath, 'w')) !== FALSE) {
      fputcsv($handle, $header); // Write the header back
      foreach ($unique as $line) {
        fputcsv($handle, $line);
      }
      fclose($handle);
      $this->output()->writeln("Duplicates removed from file: $filePath");
    }
  }

  public function processRows($rows) {
    $text_row_ids = [1, 21, 22, 23, 47, 54];
    /** @var \Drupal\citizen_migrate\Services\CmTools $cmTools */
    $cmTools = \Drupal::service('cm.tools');

    foreach ($rows as $index => $row) {
      if (!in_array($row['row_id'],$text_row_ids)) {
        continue;
      }
      $this->image_ids['row_id'] = $row['row_id'];
      $this->doc_links['row_id'] = $row['row_id'];
      $this->vid_links['row_id'] = $row['row_id'];
      $this->image_ids['rowIndex'] = $index;
      $this->doc_links['rowIndex'] = $index;
      $this->vid_links['rowIndex'] = $index;

      switch ($row['row_id']) {
        case "1":
          $this->logImageIdsInHtml($row['standard_rte']['content'], $index);
          break;

        case "21":
          $this->logImageIdsInHtml($row['tabular_rte']['content'], $index);
          break;

        case "22":
        case "23":
          $this->logImageIdsInHtml($row['standard_rte1']['content'], $index);
          $this->logImageIdsInHtml($row['standard_rte2']['content'], $index);
          if ($row['row_id'] == '23') {
            $this->logImageIdsInHtml($row['standard_rte3']['content'], $index);
          }
          break;

        case "47":
          foreach ($row['gallery']['photos'] as $i => $photo) {
            $this->logImageData($photo['val']['image_id'], $this->image_ids, $this->outputFile);
          }
          break;

        case "54":
          $this->logImageIdsInHtml($row['featured_copy']['content'], $index);
          break;
      }
    }
    $this->image_ids['row_id'] = 'n/a';
    $this->doc_links['row_id'] = 'n/a';
    $this->vid_links['row_id'] = 'n/a';
  }

  /**
   * Extracts image IDs from HTML markup and appends them to a CSV file.
   *
   * @param string $html The HTML markup.
   * @param mixed $csvFilePath Path to the CSV file.
   */
  public function logImageIdsInHtml(mixed $html, $rowIndex) {
    if (empty($html)) {
      return;
    }
    /** @var \Drupal\citizen_migrate\Services\CmTools $cmTools */
    $cmTools = \Drupal::service('cm.tools');
    $dom = new \DOMDocument();

    // Suppress errors due to malformed HTML
    libxml_use_internal_errors(true);
    $dom->loadHTML($html);
    libxml_clear_errors();

    // Find all <img> tags
    $images = $dom->getElementsByTagName('img');

    // Open the CSV file for appending
    foreach ($images as $img) {
      // Check if the image tag has the 'data-image_id' attribute
      if ($img->hasAttribute('data-image_id')) {
        $image_id = $img->getAttribute('data-image_id');

        // Append the image ID to the file
        $this->image_ids['rowIndex'] = $rowIndex;
        $this->logImageData($image_id, $this->image_ids, $this->outputFile);
        $this->images_from_text++;
      }
    }

    // Find all <a> tags.
    $links = $dom->getElementsByTagName('a');
    foreach ($links as $a) {
      if (
        $a->hasAttribute('href') &&
        preg_match('/.+uwec\.edu/', $a->getAttribute('href')) &&
        !preg_match('/^mailto:/', $a->getAttribute('href'))
      ) {
        $this->doc_links['rowIndex'] = $rowIndex;
        $this->doc_links['docUrl'] = $a->getAttribute('href');
        if (preg_match('/\.[cdflmoprstx]+$/', $a->getAttribute('href'))) {
          $cmTools->logToFile($this->doc_links, 'public://source-data/doc_links.csv', '');
        }
        else {
          $cmTools->logToFile($this->doc_links, 'public://source-data/page_links.csv', '');
        }
//        switch (TRUE) {
//          case preg_match('/^mailto:/', $a->getAttribute('href')):
//            break;
//
//          case preg_match('/.+uwec\.edu.+\.[cdflmoprstx]+$/', $a->getAttribute('href')):
//            break;
//
//          case preg_match('/.+uwec\.edu/', $a->getAttribute('href')) && !preg_match('/\.[cdflmoprstx]+$/', $a->getAttribute('href')):
//            break;
        }
      }

      // Local documents.
//      if (
//        $a->hasAttribute('href') &&
//        preg_match('/.+uwec\.edu.+\.[cdflmoprstx]+/', $a->getAttribute('href'))
//      ) {
//        $this->doc_links['rowIndex'] = $rowIndex;
//        $this->doc_links['docUrl'] = $a->getAttribute('href');
//        $cmTools->logToFile($this->doc_links, 'public://source-data/doc_links.csv', '');
//      }
//      // Local web pages (urls).
//      if (
//        $a->hasAttribute('href') &&
//        preg_match('/.+uwec\.edu/', $a->getAttribute('href')) &&
//        !preg_match('/.+uwec\.edu.+\.[cdflmoprstx]+/', $a->getAttribute('href'))
//      ) {
//        $this->doc_links['rowIndex'] = $rowIndex;
//        $this->doc_links['docUrl'] = $a->getAttribute('href');
//        $cmTools->logToFile($this->doc_links, 'public://source-data/page_links.csv', '');
//      }
//
//    }

    $iframes = $dom->getElementsByTagName('iframe');
    foreach ($iframes as $iframe) {
      $this->vid_links['rowIndex'] = $rowIndex;
      $this->vid_links['vidUrl'] = $iframe->getAttribute('src');
      if ($iframe->hasAttribute('title')) {
        $this->vid_links['title'] = $iframe->getAttribute('title');
      }
      $cmTools->logToFile($this->vid_links, 'public://source-data/vid_links.csv', '');
    }
  }
}