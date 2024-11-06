<?php
namespace Drupal\citizen_profile_sync;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;
use Drupal\Core\Database\Database;

/**
 * Syncs Drupal users and their profiles with Athena profiles endpoint data
 */
class ProfileSyncService {
  /**
   * Active database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $database;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a ProfileSyncService object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Loop through the Athena profiles data, and create, update,
   *  or deactivate/unpublish associated users and profile nodes.
   *
   * @param array $profiles
   *
   * @return void
   */
  public function syncProfiles(array $profiles) {
    foreach ($profiles as $athenaId => $profile) {
      $user = $this->findExistingUser($athenaId);
      $existingNode = $this->findExistingProfile($athenaId);
      $athenaUpdateTime = strtotime($profile->updated_at);

      // create or update User
      if ($user) {
        // Update existing User entity, if endpoint data has been updated since last import
        $userUpdateTime = $user->get('field_last_imported')->getString();
        if ($athenaUpdateTime >= strtotime($userUpdateTime)) {
          $user = $this->updateUser($user, $profile);
          $this->createOrUpdateAuthMapRecord($user);
        }
      } else {
        // no User exists. create one if this profile is active.
        if($profile->isactive){
          $user = $this->createUser($profile, $existingNode);
          $this->createOrUpdateAuthMapRecord($user);
        }
      }

      // create or update Profile
      if ($existingNode) {
        // Update existing Profile node, if endpoint data has been updated since last import
        $drupalImportTime = $existingNode->get('field_import_date')->getString();
        if ($athenaUpdateTime >= strtotime($drupalImportTime)) {
          $this->updateProfile($existingNode, $profile);
        }
      } else {
        // no profile node exists. create one if this profile is active
        if($profile->isactive){
          $this->createProfile($profile, $athenaId, $user->id());
        }
      }
    }
  }

  /**
   * Find existing user profile by AthenaId.
   *
   * @param $athenaId
   *
   * @return \Drupal\Core\Entity\EntityInterface|\Drupal\node\NodeInterface|false
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  protected function findExistingProfile($athenaId) {
    $node_storage = $this->entityTypeManager->getStorage('node');

    $query = $node_storage->getQuery()
      ->condition('type', 'bios')
      ->condition('field_athena_id', $athenaId)
      ->range(0, 1);

    $nids = $query->accessCheck(false)->execute();

    if (!empty($nids)) {
      $existing_node = $node_storage->load(reset($nids));
      return $existing_node instanceof NodeInterface ? $existing_node : false;
    }

    return false;
  }

  /**
   * Create new user profile using Athena sync field data.
   *
   * @param $profileData
   * @param $athenaId
   *
   * @return \Drupal\Core\Entity\ContentEntityBase|\Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\node\Entity\Node
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createProfile($profileData, $athenaId, $userId) {
    $node_values = [
      'type' => 'bios',
      'field_athena_id' => $athenaId,
      'field_active' => $profileData->isactive,
      'field_username' => $profileData->username,
      'field_email' => $profileData->email,
      'field_phone' => $profileData->preferred_phone,
      'field_first_name' => $profileData->first_name,
      'field_last_name' => $profileData->last_name,
      'field_position' => $profileData->hrs_title_formatted,
      'field_import_date' => $profileData->updated_at,
    ];

    $node = Node::create($node_values);
    $node->setOwnerId($userId);

    // set moderation state based on athena's "isactive" field
    if($profileData->isactive){
      $node->set('moderation_state', 'published');
    }else{
      $node->set('moderation_state', 'archived');
    }

    $node->save();

    return $node;
  }

  /**
   * Update existing profile with Athena sync field data.
   *
   * @param \Drupal\node\Entity\Node $existingNode
   * @param $profileData
   *
   * @return void
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function updateProfile(Node $existingNode, $profileData) {
    $existingNode->set('field_active', boolval($profileData->isactive));
    $existingNode->set('field_username', $profileData->username);
    $existingNode->set('field_email', $profileData->email);
    $existingNode->set('field_phone', $profileData->preferred_phone);
    $existingNode->set('field_first_name', $profileData->first_name);
    $existingNode->set('field_last_name', $profileData->last_name);
    $existingNode->set('field_position', $profileData->hrs_title_formatted);
    $existingNode->set('field_import_date', $profileData->updated_at);

    // set moderation state based on athena's "isactive" field
    if($profileData->isactive){
      $existingNode->set('moderation_state', 'published');
    }else{
      $existingNode->set('moderation_state', 'archived');
    }

    $existingNode->save();
  }


  /**
   * Get current UTC datetime and format to ISO 8601 (no offset).
   *
   * @return string
   */
  protected function getUpdateTime() {
    $now = DrupalDateTime::createFromTimestamp(time());
    $now->setTimezone(new \DateTimeZone('UTC'));

    return $now->format('Y-m-d\TH:i:s');
  }

  /**
   * Find existing Drupal user by Athena username field value.
   *
   * @param $athenaId
   * @param bool $activeOnly
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\user\Entity\User|\Drupal\user\UserInterface|false
   */
  protected function findExistingUser($athenaId) {
    $query = \Drupal::entityQuery('user')
      ->condition('field_athena_id', $athenaId)
      ->range(0, 1);

    $uids = $query->accessCheck(false)->execute();

    if (!empty($uids)) {
      $existing_user = User::load(reset($uids));
      return $existing_user instanceof UserInterface ? $existing_user : false;
    }

    return false;
  }

  /**
   * Create new Drupal user using Athena profiles sync fields data.
   *
   * @param $profile
   *
   * @return \Drupal\Core\Entity\ContentEntityBase|\Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\user\Entity\User
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createUser($profile, $existingNode = false) {
    $newuser = [
      'name' => $profile->username,
      'mail' => $profile->email,
      'field_athena_id' => $profile->id,
      'field_first_name' => $profile->first_name,
      'field_last_name' => $profile->last_name,
      'field_last_imported' => $this->getUpdateTime(),
      // Generate random password
      'pass' => \Drupal::service('password_generator')->generate(),
      'status' => 1,
      'roles' => ['personnel'],
    ];

    $user = User::create($newuser);

    //todo try-catch for failures
    $user->save();

    if ($existingNode instanceof NodeInterface) {
      $existingNode->setOwnerId($user->id());
      $existingNode->save();
    }

    return $user;
  }

  /**
   * Update existing Drupal user fields from Athena profiles sync fields data.
   *
   * @param $user
   * @param $profile
   *
   * @return void
   */
  protected function updateUser($user, $profile) {
    $user->set('name', $profile->username);
    $user->set('mail', $profile->email);
    $user->set('field_first_name', $profile->first_name);
    $user->set('field_last_name', $profile->last_name);
    $user->set('field_last_imported', $this->getUpdateTime());
    $user->save();

    return $user;
  }

  /**
   * Creates or updates authmap record for active users.
   *
   * @param $user
   *
   * @return void
   * @throws \Exception
   */
  protected function createOrUpdateAuthMapRecord($user) {
    $uid = $user->id();
    $username = $user->getAccountName();

    if ($uid) {
      // User exists, update authmap.
      $authname = $this->getExistingAuthname($uid);

      if (!$authname) {
        // Authmap record doesn't exist, create a new one.
        Database::getConnection()
          ->insert('authmap')
          ->fields([
            'uid' => $uid,
            'provider' => 'saml_auth',
            'authname' => $username,
            'data' => 'N;',
          ])
          ->execute();
      } elseif ($authname !== $username) {
        // Athena username has changed, update authname to match
        Database::getConnection()
          ->update('authmap')
          ->fields(['authname' => $username])
          ->condition('uid', $uid)
          ->execute();
      }
    }
  }

  /**
   * Returns existing authname from authmap or false if no record.
   *
   * @param $uid
   *
   * @return array|bool|mixed
   */
  protected function getExistingAuthname($uid) {
    $query = Database::getConnection()
      ->select('authmap', 'am')
      ->fields('am', ['authname'])
      ->condition('am.uid', $uid);

    $result = $query->execute();
    $resultArray = $result->fetchAssoc();

    return $resultArray ? reset($resultArray) : $resultArray;
  }
}