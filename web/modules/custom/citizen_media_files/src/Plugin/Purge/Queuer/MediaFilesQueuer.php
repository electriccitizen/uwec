<?php

namespace Drupal\citizen_media_files\Plugin\Purge\Queuer;

use Drupal\purge\Plugin\Purge\Queuer\QueuerBase;
use Drupal\purge\Plugin\Purge\Queuer\QueuerInterface;

/**
 * Queues file URL invalidations when media files are replaced or deleted.
 *
 * @PurgeQueuer(
 *   id = "citizen_media_files",
 *   label = @Translation("Citizen Media Files"),
 *   description = @Translation("Queues edge cache purges for file URLs when files are replaced or deleted."),
 *   enable_by_default = true,
 *   configform = "",
 * )
 */
class MediaFilesQueuer extends QueuerBase implements QueuerInterface {

}
