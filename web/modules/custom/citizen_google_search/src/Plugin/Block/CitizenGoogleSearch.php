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
        break;

      default:
        $results_page = '/search';
        $site = 'UWEC';
        $js_sha = '559b459c6af5e4072';
    }
    return [
      '#theme' => 'citizen_google_search_block',
      '#domain' => $domain,
      '#results_page' => $results_page,
      '#site' => $site,
      '#js_sha' => '559b459c6af5e4072',
    ];
  }

}
