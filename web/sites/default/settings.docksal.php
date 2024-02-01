<?php
# Docksal DB connection settings.
$databases['default']['default'] = array (
  'database' => 'default',
  'username' => 'user',
  'password' => 'user',
  'host' => 'db',
  'driver' => 'mysql',
);
$databases['migrate']['default'] = array (
  'database' => 'migrate',
  'username' => 'user',
  'password' => 'user',
  'host' => 'db',
  'driver' => 'mysql',
);

$settings['hash_salt'] = 'I wish i were a boy in France.';

// Site Specific URL
$base_url = "http://uwec.docksal.site";
$settings['trusted_host_patterns'] = array('^uwec\.docksal\.site', '^example\.uwec\.docksal\.site');


$settings['container_yamls'][] = DRUPAL_ROOT . '/sites/development.services.yml';

$config['system.logging']['error_level'] = 'verbose';

$settings['cache']['bins']['discovery_migration'] = 'cache.backend.memory';

$settings['rebuild_access'] = TRUE;

$settings['skip_permissions_hardening'] = TRUE;

$settings['file_temporary_path'] = '/tmp';
$settings['file_public_path'] = 'sites/default/files';
$settings['file_private_path'] = 'sites/default/files/private';
$settings['uwec_api_key'] = '36dadd0fab31edb063666ef8f43e595d';
