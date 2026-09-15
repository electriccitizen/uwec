<?php
namespace Drupal\profile_sync;
use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\Core\Database\Database;

/**
 * Syncs Drupal users and their profiles with the feed file from CampS
 * You can retrieve an object of this class with \Drupal::service('profile_sync.sync')
 */
class SyncService {
	// gets the most recent data and performs a sync on Users and Profiles.
	public function run() {
		$data = $this->fetchData();
		if(empty($data)) return;

		// keep track of all profile IDs so we can un-publish all the other ones later
		$profile_ids_in_feed = [];

		// create/update/unpublish Users and Profiles
		foreach($data as $row){
			$user = $this->findExistingUser($row['camps_empl_id'], $row['username']);
			$profile = $this->findExistingProfile($row['camps_empl_id']);

			// create or update user
			if($user){
				$this->updateUser($user, $row);
			}else{
				$user = $this->createUser($row, $profile);
			}

			// stop if we don't have a valid user
			if($user == null){
				\Drupal::logger('profile_sync')->error('Unable to import profile for user '.$row['username'].' because something went wrong with creating/update the $user record.');
				continue;
			}

			// we either just created the user, or the username may have changed,
			// so let's give this a chance to create or update the authmap
			$this->createOrUpdateAuthMapRecord($user);

			// create or update profile
			if($profile){
				$this->updateProfile($profile, $row);
			}else{
				$profile = $this->createProfile($row);
			}

			// stop if we don't have a valid profile
			if($profile == null){
				\Drupal::logger('profile_sync')->error('Unable to create/update profile for user '.$row['username']);
				continue;
			}

			// in this context, we have a valid $user and $profile

			// make sure the profile's ownerId is this user's id
			if($profile->getOwnerId() != $user->id()){
				$profile->setOwnerId($user->id());
				$profile->save();
			}

			// remember the profile id
			$profile_ids_in_feed[] = $profile->id();
		}

		// next, deactivate all profiles that are not in the feed,
		// and are set to "automatic"
		$this->unpublishProfiles($profile_ids_in_feed);
	}

	// returns the profile node for the given $empl_id
	// returns false if there isn't one.
	protected function findExistingProfile($empl_id) {
		$node_storage = \Drupal::entityTypeManager()->getStorage('node');

		$query = $node_storage->getQuery()
			->condition('type', 'bios')
			->condition('field_empl_id', $empl_id)
			->range(0, 1);

		$nids = $query->accessCheck(false)->execute();

		if(!empty($nids)){
			$profile = $node_storage->load(reset($nids));
			return $profile instanceof NodeInterface ? $profile : false;
		}

		return false;
	}

	// create a new profile based on the info in $row
	// returns the new profile
	protected function createProfile($row){
		$profile = Node::create([
			'type' => 'bios',
			'field_empl_id' => $row['camps_empl_id'],
			'field_campus_id' => $row['campus_id'],
			'field_username' => $row['username'],
			'field_email' => $row['email'],
			'field_phone' => $row['phone'],
			'field_first_name' => $row['first_name'],
			'field_last_name' => $row['last_name'],
			'field_position' => $row['title'],
			'field_active' => true,
		]);

		try{
			$profile->save();
		}catch(\Exception $e){
			\Drupal::logger('profile_sync')->error('Could not create profile with EmplID: "'.$row['camps_empl_id'].'" Username: "'.$row['username'].'" because '.$e->getMessage());
		}

		return $profile;
	}

	// updates an existing profile node with the given data
	protected function updateProfile(Node $profile, $row) {
		// don't update anything if field_active is false
		if(empty($profile->field_active->getString())){
			return;
		}

		// TODO delete this one after prod profiles have campus IDs
		$profile->set('field_campus_id', $row['campus_id']);
		$profile->set('field_username', $row['username']);
		$profile->set('field_email', $row['email']);
		$profile->set('field_phone', $row['phone']);
		$profile->set('field_first_name', $row['first_name']);
		$profile->set('field_last_name', $row['last_name']);
		$profile->set('field_position', $row['title']);

		// set to published.
		// this is for profiles who drop out of the feed (thereby getting un-published)
		// ... and then come back. they need to be auto-published.
		if(!$profile->isPublished()){
			$profile->setPublished();
		}

		try{
			$profile->save();
		}catch(\Exception $e){
			\Drupal::logger('profile_sync')->error('Could not update profile with EmplID: "'.$row['camps_empl_id'].'" Username: "'.$row['username'].'" because '.$e->getMessage());
		}
	}

	// returns a User object for the given $empl_id
	// otherwise, returns null
	protected function findExistingUser($empl_id, $username){
		$uids = \Drupal::entityQuery('user')
			->condition('field_empl_id', $empl_id)
			->range(0, 1)
			->accessCheck(false)
			->execute();
		if (!empty($uids)) {
			$user = User::load(reset($uids));
			if($user instanceof UserInterface){
				return $user;
			}
		}
		// in this context, a user was not found by empl_id

		// let's see if there is a User with a matching username and blank empl_id
		$uids = \Drupal::entityQuery('user')
			->condition('name', $username)
			->range(0, 1)
			->accessCheck(false)
			->execute();
		if(!empty($uids)){
			$user = User::load(reset($uids));
			if($user instanceof UserInterface){
				if(empty($user->field_empl_id->value)){
					// update this user's empl_id
					$user->set('field_empl_id', $empl_id);
					$user->save();

					return $user;
				}else{
					\Drupal::logger('profile_sync')->error('User "'.$username.'" (empl_id "'.$empl_id.'") found User record by username but that User has empl_id "'.$user->field_empl_id.'"');
				}
			}
		}
	}

	// creates a new user based on the info in $row and returns it.
	protected function createUser($row) {
		// generate random password
		$pass = \Drupal::service('password_generator')->generate();

		// create the new user
		$user = User::create([
			'name' => $row['username'],
			'mail' => $row['email'],
			'field_empl_id' => $row['camps_empl_id'],
			'field_campus_id' => $row['campus_id'],
			'field_first_name' => $row['first_name'],
			'field_last_name' => $row['last_name'],
			'pass' => $pass,
			'status' => 1,
			'roles' => ['personnel'],
		]);

		try{
			$user->save();
		}catch(\Exception $e){
			\Drupal::logger('profile_sync')->error('Could not import user with EmplID: "'.$row['camps_empl_id'].'" Username: "'.$row['username'].'" because '.$e->getMessage());
		}

		return $user;
	}

	// updates the given $user with the given data in $row
	protected function updateUser($user, $row) {
		try{
			$user->set('name', $row['username']);
			$user->set('mail', $row['email']);
			// TODO delete this after users in prod db have campus_id
			$user->set('field_campus_id', $row['campus_id']);
			$user->set('field_first_name', $row['first_name']);
			$user->set('field_last_name', $row['last_name']);
			$user->save();
		}catch(\Exception $e){
			\Drupal::logger('profile_sync')->error('Could not update user with EmplID: "'.$row['camps_empl_id'].'" Username: "'.$row['username'].'" because '.$e->getMessage());
		}
	}

	// creates or updates the authmap record for the given $user
	// call this whenever a new user is created, or if the username might have updated.
	protected function createOrUpdateAuthMapRecord($user) {
		$uid = $user->id();
		$username = $user->getAccountName();

		if($uid){
			// User exists, update authmap.
			$authname = $this->getExistingAuthname($uid);
			
			// this is the format that we get from the idp
			$desired_authname = strtoupper($username).'@uwec.edu';

			if (!$authname) {
				// Authmap record doesn't exist, create a new one.
				try{
					Database::getConnection()
						->insert('authmap')
						->fields([
							'uid' => $uid,
							'provider' => 'saml_auth',
							'authname' => $desired_authname,
							'data' => 'N;',
						])
						->execute();
				}catch(\Exception $e){
					\Drupal::logger('profile_sync')->error('Could not create authmap record for uid: "'.$uid.'" authname: "'.$desired_authname.'" because '.$e->getMessage());
				}
			} elseif ($authname !== $desired_authname) {
				// username has changed, update authname to match
				try{
					Database::getConnection()
						->update('authmap')
						->fields(['authname' => $desired_authname])
						->condition('uid', $uid)
						->execute();
				}catch(\Exception $e){
					\Drupal::logger('profile_sync')->error('Could not update authmap record for uid: "'.$uid.'" authname: "'.$desired_authname.'" because '.$e->getMessage());
				}
			}
		}else{
			\Drupal::logger('profile_sync')->error('Tried to create/update authmap for user that does not exist! (username: '.$username.')');
		}
	}

	// returns the existing authname for the given $uid (user id)
	// returns false if there isn't one.
	protected function getExistingAuthname($uid) {
		$query = Database::getConnection()
			->select('authmap', 'am')
			->fields('am', ['authname'])
			->condition('am.uid', $uid);

		$result = $query->execute();
		$resultArray = $result->fetchAssoc();

		return $resultArray ? reset($resultArray) : $resultArray;
	}

	// un-publishes all "automatic" profiles other than the given profile nids.
	protected function unpublishProfiles($profile_ids_in_feed){
		$node_storage = \Drupal::entityTypeManager()->getStorage('node');

		$query = $node_storage->getQuery()
			->condition('type', 'bios')
			->condition('status', 1) // only published
			->condition('field_active', 1) // only unpublish "automatic" profiles
			->condition('nid', $profile_ids_in_feed, 'NOT IN'); // exclude profiles currently in the feed

		// get all the nids to unpublish
		$nids = $query->accessCheck(false)->execute();
		if(empty($nids)) return;

		// unpublish them
		foreach($nids as $nid){
			$profile = $node_storage->load($nid);
			$profile->setUnpublished();
			$profile->save();
		}
	}

	// returns a big array of all the current profiles data.
	// returns null if unsuccessful.
	function fetchData(){
		// we can only fetch data if we can get the apikey from pantheon secrets
		if(!function_exists('pantheon_get_secret')) return;
		$apikey = pantheon_get_secret('profiles_data_apikey');

		$url = 'https://profilesdata.apps.uwec.edu/?apikey='.$apikey;

		try{
			$response = \Drupal::httpClient()->request('GET', $url, [
				'timeout'=>10,
				'headers'=>[
					'Accept'=>'application/json',
				],
			]);

			if($response->getStatusCode() == 200){
				$data = json_decode($response->getBody()->getContents(), true);

				// lowercase usernames
				foreach($data as $key=>$dat){
					$data[$key]['username'] = strtolower($dat['username']);
				}

				return $data;
			}
		}catch(\Exception $e){
			\Drupal::logger('profile_sync')->error($e->getMessage());
		}
	}
}