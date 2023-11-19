<?php

namespace Drupal\citizen_profile_sync;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\datetime\Plugin\Field\FieldType\DateTimeItemInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\node\NodeInterface;

class ProfileSyncService {

  const IGNORE_BEFORE_DATE = '2023-11-01 00:00:00';
  protected $entityTypeManager;


  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  public function syncProfiles(array $profiles) {
    foreach ($profiles as $athenaId => $profile) {
      // Process $profileData and extract necessary information.

      // Check if a Profile node with a unique identifier already exists.
      $existingNode = $this->findExistingProfile($athenaId);

      if ($existingNode) {
        // Update existing Profile node, if endpint data has been updated since last import
         $athenaUpdateTime = $profile->updated_at;
         $drupalImportTime = $existingNode->get('field_import_date')->getString();
        if (strtotime($athenaUpdateTime) >= strtotime($drupalImportTime)) {
          $this->updateProfile($existingNode, $profile);
        }
      }
      else {
        // Create a new Profile node.
        $this->createProfile($profile, $athenaId);
      }
    }
    $bar = 1;
  }

  protected function findExistingProfile($athenaId) {
    // Implement logic to find an existing Profile node based on a unique identifier.
    $node_storage = $this->entityTypeManager->getStorage('node');

    // Query the database for a profile node with the unique identifier.
    $query = $node_storage->getQuery()
      ->condition('type', 'bios') // Adjust the content type if needed.
      ->condition('field_athena_id', $athenaId)
      ->range(0, 1);

    $nids = $query->accessCheck(false)->execute();

    if (!empty($nids)) {
      // Load the first matching profile node.
      $existing_node = $node_storage->load(reset($nids));
      return $existing_node instanceof NodeInterface ? $existing_node : NULL;
    }

    return NULL;
  }



  protected function createProfile($profileData, $athenaId) {
    // Prepare the node values based on the $profileData.
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

    // Create a new profile node.
    $node = Node::create($node_values);

    // Set the author of the node to Admin.
    $node->setOwnerId(1);

    // Save the node to the database.
    $node->save();

    //todo enable when Adam says ok
    //todo figure out why this doesn't work
//    $node->set('status', NodeInterface::PUBLISHED);
//    $node->save();

    // Return the newly created node.
    return $node;

  }

  protected function updateProfile(Node $existingNode, $profileData) {
    // Update the node values based on the $profileData.
    $existingNode->set('field_active', true);
    $existingNode->set('field_username', $profileData->username);
    $existingNode->set('field_email', $profileData->email);
    $existingNode->set('field_phone', $profileData->preferred_phone);
    $existingNode->set('field_first_name', $profileData->first_name);
    $existingNode->set('field_last_name', $profileData->last_name);
    // todo department No per Adam https://ecitizen.atlassian.net/browse/UWEC-64
    // todo office location No per Adam https://ecitizen.atlassian.net/browse/UWEC-64
    $existingNode->set('field_position', $profileData->hrs_title_formatted);
    //todo change field def to include time as well as date. Need full timestamp to check to see if data needs to be imported
    $existingNode->set('field_import_date', $this->getUpdateTime());
    //todo enable when Adam says ok
//    $existingNode->set('status', NodeInterface::PUBLISHED);

    // Save the updated node to the database.
    $existingNode->save();
  }

  public function deactivateProfiles($profiles) {
    foreach ($profiles as $athenaId => $profile) {

      //todo skip all with updated_at datetimes before current dev date
      //todo adjust that date later closer to launch
      $athenaUpdateTime = $profile->updated_at;

      if (strtotime($athenaUpdateTime) > strtotime(self::IGNORE_BEFORE_DATE)) {
        $existingNode = $this->findExistingProfile($athenaId);
        if ($existingNode) {
          $drupalImportTime = $existingNode->get('field_import_date')->getString();
          if (strtotime($athenaUpdateTime) >= strtotime($drupalImportTime)) {
          //todo remove after testing
//          if (true or strtotime($athenaUpdateTime) >= strtotime($drupalImportTime)) {
          //todo end test code
            $existingNode->set('field_active', 0);
            $existingNode->set('field_import_date', $this->getUpdateTime());
            $existingNode->set('status', NodeInterface::NOT_PUBLISHED);
            $existingNode->save();
          }
        }
        else {
          // this should never happen
        }
      }



    }
  }

  protected function getUpdateTime() {
    $now = DrupalDateTime::createFromTimestamp(time());
    $now->setTimezone(new \DateTimeZone('UTC'));

    return $now->format('Y-m-d\TH:i:s');
  }

}