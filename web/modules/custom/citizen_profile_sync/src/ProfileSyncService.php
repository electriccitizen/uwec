<?php

namespace Drupal\citizen_profile_sync;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;
use Drupal\user\Entity\User;
use Drupal\user\UserInterface;

/**
 * Syncs Drupal users and their profiles with Athena profiles endpoint data
 */
class ProfileSyncService {

  const IGNORE_BEFORE_DATE = '2023-11-01 00:00:00';
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
      $existingUser = $this->findExistingUser($profile->username);
      $existingNode = $this->findExistingProfile($athenaId);

      if ($existingUser ||  $existingNode) {
        $athenaUpdateTime = $this->convertToUTC($profile->updated_at);
      }

      if ($existingUser) {
        // Update existing User entity, if endpoint data has been updated since last import
        $userUpdateTime = $existingUser->get('field_last_imported')->getString();
        if (strtotime($athenaUpdateTime) >= strtotime($userUpdateTime)) {
          //todo remove after testing
//          if (true or strtotime($athenaUpdateTime) >= strtotime($userUpdateTime)) {
          //todo end test code
          $this->updateUser($existingUser, $profile);
        }
      } else {
        $this->createUser($profile);
        // todo create authmap record
        // $this->>createAuthMapRecord();
      }

      if ($existingNode) {
        // Update existing Profile node, if endpoint data has been updated since last import
         $drupalImportTime = $existingNode->get('field_import_date')->getString();
        if (strtotime($athenaUpdateTime) >= strtotime($drupalImportTime)) {
          $this->updateProfile($existingNode, $profile);
        }
      }
      else {
        $this->createProfile($profile, $athenaId);
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
  protected function createProfile($profileData, $athenaId) {
    $node_values = [
      'type' => 'bios',
      'field_athena_id' => $athenaId,
      'field_active' => 1,
      'field_username' => $profileData->username,
      'field_email' => $profileData->email,
      'field_phone' => $profileData->preferred_phone,
      'field_first_name' => $profileData->first_name,
      'field_last_name' => $profileData->last_name,
      // todo department? No per Adam https://ecitizen.atlassian.net/browse/UWEC-64
      // todo office location? No per Adam https://ecitizen.atlassian.net/browse/UWEC-64
      'field_position' => $profileData->hrs_title_formatted,
      'field_import_date' => $this->getUpdateTime(),
    ];

    $node = Node::create($node_values);
    $node->setOwnerId(1);
    $node->save();

    //todo enable when Adam says ok
    //todo figure out why this doesn't work
//    $node->set('status', NodeInterface::PUBLISHED);
//    $node->save();

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
    $existingNode->set('field_active', true);
    $existingNode->set('field_username', $profileData->username);
    $existingNode->set('field_email', $profileData->email);
    $existingNode->set('field_phone', $profileData->preferred_phone);
    $existingNode->set('field_first_name', $profileData->first_name);
    $existingNode->set('field_last_name', $profileData->last_name);
    // todo department No per Adam https://ecitizen.atlassian.net/browse/UWEC-64
    // todo office location No per Adam https://ecitizen.atlassian.net/browse/UWEC-64
    $existingNode->set('field_position', $profileData->hrs_title_formatted);
    $existingNode->set('field_import_date', $this->getUpdateTime());
    //todo enable when Adam says ok
//    $existingNode->set('status', NodeInterface::PUBLISHED);

    $existingNode->save();
  }

  /**
   * Unpublish existing profiles for users who have been flagged as inactive in Athena.
   *
   * @param $profiles
   *
   * @return void
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function deactivateProfiles($profiles) {
    foreach ($profiles as $athenaId => $profile) {
      $athenaUpdateTime = $this->convertToUTC($profile->updated_at);
      //Skip all with updated_at datetimes before current dev date
      //todo adjust the date constant before launch
      if (strtotime($athenaUpdateTime) > strtotime(self::IGNORE_BEFORE_DATE)) {
      //todo remove test code after testing
//      if (true || (strtotime($athenaUpdateTime) > strtotime(self::IGNORE_BEFORE_DATE))) {
        // todo end test code
        $existingActiveUser = $this->findExistingUser($profile->username, true);

        if ($existingActiveUser) {
          $existingActiveUser->block();
          $existingActiveUser->set('field_last_imported', $this->getUpdateTime());
          $existingActiveUser->save();
        }

        $existingNode = $this->findExistingProfile($athenaId);

        if ($existingNode) {
          $drupalImportTime = $existingNode->get('field_import_date')->getString();
          if (strtotime($athenaUpdateTime) >= strtotime($drupalImportTime)) {
          //todo remove after testing
//          if (true || strtotime($athenaUpdateTime) >= strtotime($drupalImportTime)) {
          //todo end test code
            $existingNode->set('field_active', 0);
            $existingNode->set('field_import_date', $this->getUpdateTime());
            $existingNode->set('status', NodeInterface::NOT_PUBLISHED);
            $existingNode->save();
          }
        }
      }
    }
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
   * Convert CST/CDT datetime string to UTC.
   *
   * @param $datetimeStringCST
   *
   * @return string
   */
  protected function convertToUTC($datetimeStringCST) {
    $datetimeCST = new DrupalDateTime($datetimeStringCST, 'America/Chicago');
    $datetimeCST->setTimezone(new \DateTimeZone('UTC'));

    return $datetimeCST->format('Y-m-d H:i:s');
  }

  /**
   * Find existing Drupal user by Athena username field value.
   *
   * @param $username
   * @param bool $activeOnly
   *
   * @return \Drupal\Core\Entity\EntityBase|\Drupal\Core\Entity\EntityInterface|\Drupal\user\Entity\User|\Drupal\user\UserInterface|false
   */
  protected function findExistingUser($username, bool $activeOnly = false) {
    $query = \Drupal::entityQuery('user')
      ->condition('name', $username)
      ->range(0, 1); // Limit the result to 1 record.

    if ($activeOnly) {
      $query->condition('status', 1);
    }

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
   * @return void
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  protected function createUser($profile) {
    $newuser = [
      'name' => $profile->username,
      'mail' => $profile->email,
      'field_first_name' => $profile->first_name,
      'field_last_name' => $profile->last_name,
      'field_last_imported' => $this->getUpdateTime(),
      // Generate random password
      'pass' => \Drupal::service('password_generator')->generate(),
      'status' => 0, // Set to 0 for inactive users.
    ];

    $user = User::create($newuser);

    //todo try-catch for failures
    $user->save();
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
    $user->set('mail', $profile->email);
    $user->set('field_first_name', $profile->first_name);
    $user->set('field_last_name', $profile->last_name);
    $user->set('field_last_imported', $this->getUpdateTime());
    $user->save();
  }

}