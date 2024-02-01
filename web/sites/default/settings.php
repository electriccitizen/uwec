<?php

/**
 * Load services definition file.
 */
$settings['container_yamls'][] = __DIR__ . '/services.yml';

/**
 * Include the Pantheon-specific settings file.
 *
 * n.b. The settings.pantheon.php file makes some changes
 *      that affect all environments that this site
 *      exists in.  Always include this file, even in
 *      a local development environment, to ensure that
 *      the site settings remain consistent.
 */
include __DIR__ . "/settings.pantheon.php";

/**
 * Skipping permissions hardening will make scaffolding
 * work better, but will also raise a warning when you
 * install Drupal.
 *
 * https://www.drupal.org/project/drupal/issues/3091285
 */
// $settings['skip_permissions_hardening'] = TRUE;

/**
 * Place the config directory outside of the Drupal root.
 */
$settings['config_sync_directory'] = "../config/sync";

/**
 * Get the api_key.
 */
$secretsFile = $_SERVER['HOME'] . '/files/private/secrets.json';
if (file_exists($secretsFile)) {
  $secrets = json_decode(file_get_contents($secretsFile), 1);
  $settings['uwec_api_key'] = $secrets['uwec_api_key'];
}

/**
 * Set up config splits
 */
if (isset($_ENV['PANTHEON_ENVIRONMENT'])) {
  $settings['file_temp_path'] = '/tmp';

  switch ($_ENV['PANTHEON_ENVIRONMENT']) {
    case 'live':
      $config['config_split.config_split.live']['status'] = TRUE;
      break;

    case 'test':
      $config['config_split.config_split.test']['status'] = TRUE;
      break;

    default:
      $config['config_split.config_split.dev']['status'] = TRUE;
//      $secretsFile = $_SERVER['HOME'] . '/files/private/secrets.json';
//      $secrets = json_decode(file_get_contents($secretsFile),  1);
//      $settings['uwec_api_key'] = $secrets['uwec_api_key'];
      break;

  }
} else {
    // LOCAL

  $config['config_split.config_split.local']['status'] = TRUE;

  /**
   * If there is a docksal settings file, then include it
   */
  $docksal_settings = __DIR__ . "/settings.docksal.php";
  if (file_exists($docksal_settings)) {
      include $docksal_settings;
  }

  /**
   * If there is a local settings file, then include it
   */
  $local_settings = __DIR__ . "/settings.local.php";
  if (file_exists($local_settings)) {
      include $local_settings;
  }
}