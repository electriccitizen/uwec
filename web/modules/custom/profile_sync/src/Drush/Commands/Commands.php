<?php
namespace Drupal\profile_sync\Drush\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Utility\Token;
use Drush\Attributes as CLI;
use Drush\Commands\AutowireTrait;
use Drush\Commands\DrushCommands;

class Commands extends DrushCommands {
	use AutowireTrait;

	#[CLI\Command(name: 'profile_sync:run')]
	#[CLI\Usage(name: 'profile_sync:run', description: 'Runs the profile sync')]
	public function run() {
		$sync = \Drupal::service('profile_sync.sync');

		// quit if there's no new file, and they don't want to run it anyway
		if(!$sync->hasNewFile()){
			if(!$this->io()->confirm('No new file. Sync anyway?', true)){
				return;
			}
		}

		// perform the sync
		$sync->run();

		// if there are old files we could delete, ask if we should delete them:
		if($sync->hasOldFiles()){
			if($this->io()->confirm('There are some old files. Delete them?', true)){
				$sync->deleteOldFiles();
			}
		}
	}
}
