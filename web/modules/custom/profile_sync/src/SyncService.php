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
	// defines how many files we want to keep around in the dir.
	const FILES_TO_KEEP = 5;

	// returns true if there is a new file that may not have been imported yet.
	public function hasNewFile() {
		return count($this->getFiles()) > self::FILES_TO_KEEP;
	}

	// returns true if there are some files that could be deleted.
	public function hasOldFiles(){
		// weirdly, this is the same logic as hasNewFile for now.
		// but it's a different question from outside this service
		// so just in case it changes, let's keep both public functions.
		return $this->hasNewFile();
	}

	// gets the most recent data and performs a sync on Users and Profiles.
	public function run() {
		// get most recent file
		$filename = $this->getFiles()[0];

		// load that file into an array
		$data = $this->getFileData($filename);

		// create/update/unpublish Users and Profiles
		foreach($data as $row){
			$user = $this->findExistingUser($row['id']);
			$profile = $this->findExistingProfile($row['id']);

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
		}
	}

	// deletes the oldest files, keeping FILES_TO_KEEP number of files in the dir.
	public function deleteOldFiles() {
		// get all files other than the first FILES_TO_KEEP number of files.
		$files = $this->getFiles();
		for($i = 0; $i < self::FILES_TO_KEEP; $i++){
			array_shift($files);
		}

		// now delete the files we still have
		$dir = $this->getDir();
		foreach($files as $file){
			unlink($dir.$file);
		}
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
			'field_empl_id' => $row['id'],
			'field_username' => $row['username'],
			'field_email' => $row['email'],
			'field_phone' => $row['phone'],
			'field_first_name' => $row['firstname'],
			'field_last_name' => $row['lastname'],
			'field_position' => $row['title'],
			'field_active' => true,
		]);

		try{
			$profile->save();
		}catch(\Exception $e){
			\Drupal::logger('profile_sync')->error('Could not create profile with EmplID: "'.$row['id'].'" Username: "'.$row['username'].'" because '.$e->getMessage());
		}

		return $profile;
	}

	// updates an existing profile node with the given data
	protected function updateProfile(Node $profile, $row) {
		// don't update anything if field_active is false
		if(empty($profile->field_active->getString())){
			return;
		}

		$profile->set('field_username', $row['username']);
		$profile->set('field_email', $row['email']);
		$profile->set('field_phone', $row['phone']);
		$profile->set('field_first_name', $row['firstname']);
		$profile->set('field_last_name', $row['lastname']);
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
			\Drupal::logger('profile_sync')->error('Could not update profile with EmplID: "'.$row['id'].'" Username: "'.$row['username'].'" because '.$e->getMessage());
		}
	}

	// returns a User object for the given $empl_id
	protected function findExistingUser($empl_id){
		$query = \Drupal::entityQuery('user')
			->condition('field_empl_id', $empl_id)
			->range(0, 1);

		$uids = $query->accessCheck(false)->execute();

		if (!empty($uids)) {
			$user = User::load(reset($uids));
			return $user instanceof UserInterface ? $user : false;
		}

		return false;
	}

	// creates a new user based on the info in $row and returns it.
	protected function createUser($row) {
		// generate random password
		$pass = \Drupal::service('password_generator')->generate();

		// create the new user
		$user = User::create([
			'name' => $row['username'],
			'mail' => $row['email'],
			'field_empl_id' => $row['id'],
			'field_first_name' => $row['firstname'],
			'field_last_name' => $row['lastname'],
			'empl_id' => $row['id'],
			'pass' => $pass,
			'status' => 1,
			'roles' => ['personnel'],
		]);

		try{
			$user->save();
		}catch(\Exception $e){
			\Drupal::logger('profile_sync')->error('Could not import user with EmplID: "'.$row['id'].'" Username: "'.$row['username'].'" because '.$e->getMessage());
		}

		return $user;
	}

	// updates the given $user with the given data in $row
	protected function updateUser($user, $row) {
		try{
			$user->set('name', $row['username']);
			$user->set('mail', $row['email']);
			$user->set('field_first_name', $row['firstname']);
			$user->set('field_last_name', $row['lastname']);
			$user->save();
		}catch(\Exception $e){
			\Drupal::logger('profile_sync')->error('Could not update user with EmplID: "'.$row['id'].'" Username: "'.$row['username'].'" because '.$e->getMessage());
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

	// returns an absolute path to the directory that contains profiles feed files
	protected function getDir() {
		// if we're on pantheon, use the sftp path
		// to see this dir, click "connection settings" in pantheon and use sftp
		if(isset($_ENV['PANTHEON_ENVIRONMENT'])){
			return '/files/private/camps_profiles_feed/';
		}

		// we are not on pantheon, so fall back to drupal root
		return DRUPAL_ROOT.'/camps_profiles_feed/';
	}

	// returns an array of filenames for all files that exist in the dir.
	protected function getFiles() {
		static $files = null;

		// if we haven't loaded the files yet, load them
		if($files === null){
			$files = scandir($this->getDir(), SCANDIR_SORT_DESCENDING);
			if($files === false){
				$files = [];
				\Drupal::logger('profile_sync')->warning('There is something wrong with the sync dir ('.$this->getDir().')');
				return $files;
			}

			// remove "." and ".."
			$files = array_filter($files, function($file){return $file != '.' && $file != '..';});
		}

		return $files;
	}

	// returns a big array with all the profile data that exists in the given $filename
	protected function getFileData($filename) {
		$filepath = $this->getDir().$filename;

		// get rows and split out the first row as the headers
		$rows = array_map('str_getcsv', file($filepath));

		$headers = array_shift($rows);
		$headers = array_map('strtolower', $headers);

		// there is a weird BOM (or something) so let's just remove all non a-z (and space) characters
		$headers = array_map(function($h){
			return preg_replace('/[^a-z]/', '', $h);
		}, $headers);

		// stick all the data in a nice array
		$csv = [];
		foreach ($rows as $row) {
			$row = array_combine($headers, $row);

			// lowercase username
			$row['username'] = strtolower($row['username']);

			// format phone number from "123/456-7899" to "123-456-7899"
			$row['phone'] = str_replace('/', '-', $row['phone']);

			$csv[] = $row;
		}

		return $csv;
	}
}