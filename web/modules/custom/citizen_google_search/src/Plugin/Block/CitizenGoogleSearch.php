<?php

namespace Drupal\citizen_google_search\Plugin\Block;

use Drupal\Core\Block\BlockBase;

/**
 * Provides the custom Google Programmable Search block.
 *
 * @Block(
 *    id = "citizen_google_search_block",
 *    admin_label = @Translation("Citizen Google Search block"),
 *   )
 */
class CitizenGoogleSearch extends BlockBase {

  /**
   * @inheritDoc
   */
  public function build() {
    // Get the domain from the URL.
    $domain = \Drupal::request()->getHost();
    // Isolate the domain/subdomain.
    preg_match('/(\w+)\./', $domain, $site);

    // @todo Modify the switch values for production.
    switch ($site[1]) {
      // The 'js_sha' comes from the Google Programmable Search Engine UI.
      // It's the query string value within the <script> tag's src attribute.
      // Example: https://cse.google.com/cse.js?cx=[sha].

      case 'example':
        $results_page = '/search-fancy-example';
        $site = 'Fancy Example';
        //$js_sha = '6657d6eee9af349d2';
        break;

      default:
        $results_page = '/search';
        $site = 'UWEC';
        $js_sha = '004626211687516433429:aex2tyveipy';
    }
    return [
      '#theme' => 'citizen_google_search_block',
      '#domain' => $domain,
      '#results_page' => $results_page,
      '#site' => $site,
      '#js_sha' => '004626211687516433429:aex2tyveipy',
      //'#js_sha' => '9446cf1add44a49ab', // for development URLs.
    ];
  }

}
