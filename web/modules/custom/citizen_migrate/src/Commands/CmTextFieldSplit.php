<?php

namespace Drupal\citizen_migrate\Commands;

use Drupal\Core\Logger\RfcLogLevel;
use GuzzleHttp\Exception\RequestException;

class CmTextFieldSplit extends \Drush\Commands\DrushCommands {

  protected string $logFile = 'public://source-data/row_text_splits.csv';

  protected array $field_splits = [
    'ent_id' => '',
    'rowIndex' => '',
    'is_figure' => 'false',
    'image_id' => '',
    'image_src' => '',
    'video_src' => '',
    'markup' => '',
  ];

  /**
   * Splits HTML markup and logs data to a file.
   *
   * @command citizen-migrate:text-field-split
   * @aliases cm-text-split
   * @usage citizen-migrate:text-field-split
   *   Splits HTML markup at <figure> tags and logs the data.
   */
  public function splitHtml() {
    $this->output()->writeln("Splitting HTML and logging data...");
    $baseURL = 'https://athena.apps.uwec.edu/api/pages.json';
    $apiKey = getenv('API_KEY');

    $url = "$baseURL?apikey=$apiKey";

    $data = $this->fetch_data($url);
    $text_row_ids = [1, 3, 21, 22, 23, 46, 54];

    foreach ($data as $item) {
      $this->field_splits['ent_id'] = $item['id'];

      foreach ($item['data'] as $key => $row) {
        if (!in_array($row['row_id'], $text_row_ids)) {
          continue;
        }
        $this->field_splits['rowIndex'] = $key;
        switch ($row['row_id']) {
          case "1":
            $this->splitAndLogHtml($row['standard_rte']['content']);
            break;
          case "3":
            $this->splitAndLogHtml($row['accordion']['content']);
            break;
          case "21":
            $this->splitAndLogHtml($row['tabular_rte']['content']);
            break;
          case "22":
          case "23":
          $this->splitAndLogHtml($row['standard_rte1']['content']);
          $this->splitAndLogHtml($row['standard_rte2']['content']);
          if ($row['row_id'] == "23") {
            $this->splitAndLogHtml($row['standard_rte3']['content']);
          }
            break;
          case "46":
            $this->splitAndLogHtml($row['wildcard']['content']);
            break;
          case "54":
            $this->splitAndLogHtml($row['featured_copy']['content']);
            break;
        }
      }
    }
//    // Example HTML content, you might want to load this from a file or other source
//    $htmlContent = "<p>Our goals are to support the mission and core values of the University through the efficient and effective acquisition of goods and services; to provide outstanding customer service to our stakeholders with commodity, sourcing, and compliance expertise; and to employ open, transparent, and ethical procurement processes.</p>\r\n<h3>Procurement Process - Overview</h3>\r\n<p>The procurement process begins when you&rsquo;re ready to make a purchase. If what you are purchasing falls under one of the categories in the bulleted list below, the dollar thresholds in the graphic shown below do not apply. If you have questions about your purchase, please work with Purchasing at <a href=\"mailto:purchasing@uwec.edu\" target=\"_blank\" rel=\"noopener\">purchasing@uwec.edu</a>.</p>\r\n<ul>\r\n<li>Special Considerations and Requirements (Printing Services, Insurance, Vehicles, Legal Services, Signage)</li>\r\n<li>Mandatory Contract (office supplies, furniture, batteries, printing, janitorial, medical supplies, computers, printers, and software)</li>\r\n<li>Non-mandatory or optional contract</li>\r\n</ul>\r\n<p>If what you are trying to purchase does not fall into any of the above categories, then we proceed based on dollar thresholds as shown below:</p>\r\n<figure class=\"center storyimage\"><img src=\"https://publicwebuploads.uwec.edu/images/12720-Screen-Shot-2019-04-08-at-8.14.59-AM-medium.png\" alt=\"purchasing authority\" data-image_id=\"undefined\">\r\n<figcaption></figcaption>\r\n</figure>\r\n<p>&nbsp;</p>\r\n<h4>Best Judgment Purchases &lt; $5,000</h4>\r\n<p>Purchases under $5,000 are considered &ldquo;Best Judgment&rdquo; and can be made using a procurement card. Procurement cards are set up with standard limits of $1,500 for a single purchase and $5,000 for a billing cycle.</p>\r\n<p><em>*The best judgment threshold for signage is $3,500. Any signage purchase exceeding this amount must be run as a Request for Bid (RFB) through the Purchasing Office.*&nbsp;</em></p>\r\n<p><strong>There are no best judgment purchases for printing.&nbsp;</strong></p>\r\n<h4>Simplified Bidding - $5,000 - $50,000</h4>\r\n<p>The requesting department must obtain three &ldquo;apples to apples&rdquo; quotes from vendors for purchases falling between the $5,000 - $50,000 range. The quotes from the three vendors must be identical, otherwise, the bids will not be accepted. You must purchase from the lowest quoted vendor. Attach the three quotes and a completed copy of <a href=\"https://doa.wi.gov/Forms/doa-3088SimplifiedBiddingRecord.pdf\" target=\"_blank\" rel=\"noopener\">DOA Form 3088, Simplified Bidding Record</a>, to the purchase requisition in ShopUW+. Please reference the flowchart on the ShopUW+ page if you are unsure which purchase process form to select.&nbsp;</p>\r\n<p>Purchasing will review the quotes and simplified bidding record and will verify that you have selected the lowest quoted vendor for your purchase based on the following information:</p>\r\n<ul>\r\n<li>Are the products available through a contract? If so, do you have a waiver from the contract?</li>\r\n<li>Are the quotes comparing the identical product?</li>\r\n<li>Is this vendor on the ineligible vendor list?</li>\r\n<li>Awarded to lowest responsible bidder</li>\r\n</ul>\r\n<p><em>*Use Minority Business Enterprise (MBE) vendors whenever possible</em></p>\r\n<h3>Request for Bid (RFB)/Request for Proposal (RFP)</h3>\r\n<p>If your department is considering a purchase of over $50,000, a public bidding process must occur through the Purchasing Office. The average length of an RFB is 1-6 months and 9 months or more for an RFP. Please involve Purchasing as early in your planning as possible for goods or services totaling more than $50,000 with these timelines in mind.</p>\r\n<p>&nbsp;</p>";
//
//    // Perform the splitting and logging operation
//    $this->splitAndLogHtml($htmlContent, $this->logFile);

    $this->output()->writeln("HTML splitting and logging completed.");
  }

  // Function to fetch data from the API
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
   * Splits HTML markup at <figure> tags containing <img> and logs parts to a file.
   *
   * @param string $html The HTML markup.
   * @param string $logFilePath Path to the log file.
   */
  public function splitAndLogHtml($html) {
    if (empty($html)) {
      return;
    }

    $html = str_replace('\r\n', '', $html);

    $dom = new \DOMDocument(1.0, 'UTF-8');

    /** @var \Drupal\citizen_migrate\Services\CmTools $cmTools */
    $cmTools = \Drupal::service('cm.tools');

    // Suppress errors due to malformed HTML
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
    libxml_clear_errors();

    $body = $dom->getElementsByTagName('body')->item(0);
    $children = $body->childNodes;

    $tempDom = new \DOMDocument();
    $output = [];

    foreach ($children as $i => $child) {
      if ($child->nodeName === 'figure') {
        // Log the current chunk before <figure>
//        $output[] = $tempDom->saveHTML();
        $this->field_splits['markup'] = $tempDom->saveHTML();
        $cmTools->logToFile($this->field_splits, $this->logFile, '');
        $this->field_splits['markup'] = ''; // Reset for next row.
        $tempDom = new \DOMDocument(); // Reset for next chunk

        // Find <img> within <figure>
        $img = $child->getElementsByTagName('img')->item(0);
        if ($img) {
//          $imageId = $img->getAttribute('data-image_id');
          $this->field_splits['image_id'] = $img->getAttribute('data-image_id');
          $this->field_splits['image_src'] = $img->getAttribute('src');
          $cmTools->logToFile($this->field_splits, $this->logFile, '');
          $this->field_splits['image_id'] = ''; // Reset for next row.
          $this->field_splits['image_src'] = ''; // Reset for next row.
//          $output[] = $imageId;
        }

        // Find the <iframe> within <figure>.
        $iframe = $child->getElementsByTagName('iframe')->item(0);
        if ($iframe)  {
          $this->field_splits['video_src'] = $iframe->getAttribute('src');
          $cmTools->logToFile($this->field_splits, $this->logFile, '');
          $this->field_splits['video_src'] = ''; // Reset for next row.
        }

        // Append the whole <figure> to output
        $figureHtml = $dom->saveHTML($child);
//        $output[] = $figureHtml;
        $this->field_splits['markup'] = $figureHtml;
        $this->field_splits['is_figure'] = 'true';
        $cmTools->logToFile($this->field_splits, $this->logFile, '');
        $this->field_splits['markup'] = ''; // Reset for next row.
        $this->field_splits['is_figure'] = 'false';
      } else {
        $tempDom->appendChild($tempDom->importNode($child, true));
      }
    }

    // Don't forget the last chunk after the last <figure>
//    $output[] = $tempDom->saveHTML();
    $this->field_splits['markup'] = $tempDom->saveHTML();
    $cmTools->logToFile($this->field_splits, $this->logFile, '');
    $this->field_splits['markup'] = ''; // Reset for next row.

    // Write to log file
//    file_put_contents($logFilePath, implode("\n", $output));
  }

}