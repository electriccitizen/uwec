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

		// perform the sync
		$this->io()->text('Starting sync...');
		$sync->run();
		$this->io()->text('..done!');
	}

	// scan profiles for dirty data
	#[CLI\Command(name: 'profile_sync:dirty')]
	#[CLI\Usage(name: 'profile_sync:dirty', description: 'Finds profiles with dirty data.')]
	public function dirty(){
		$node_storage = \Drupal::entityTypeManager()->getStorage('node');

		// query all automatic profiles
		$query = $node_storage->getQuery()
			->condition('type', 'bios')
			->condition('field_active', 1);
		$auto_nids = $query->accessCheck(false)->execute();

		// print username for all profiles with no empl_id
		echo "Checking for automatic profiles with no empl_id..\n";
		$count = 0;
		foreach($auto_nids as $nid){
			$profile = $node_storage->load($nid);
			$empl_id = $profile->field_empl_id->getString();
			if(empty($empl_id)){
				$username = $profile->field_username->getString();
				echo "$username (nid $nid)\n";
				$count++;
			}
		}
		echo "..done! Found ".$count."\n";

		// print usernames for all profiles with no author
		$query = \Drupal::entityQuery('node');
		$query->condition('type', 'bios');
		$query->condition('uid', 0);
		$nids = $query->accessCheck(false)->execute();
		echo 'There are '.count($nids)." profiles with no author\n";
		foreach($nids as $nid){
			echo "$nid\n";
		}

		echo '..done!';
	}
}
