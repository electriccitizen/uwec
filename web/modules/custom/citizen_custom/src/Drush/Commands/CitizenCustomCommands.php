<?php

namespace Drupal\citizen_custom\Drush\Commands;

use Consolidation\OutputFormatters\StructuredData\RowsOfFields;
use Drupal\Core\Utility\Token;
use Drush\Attributes as CLI;
use Drush\Commands\DrushCommands;
use Symfony\Component\DependencyInjection\ContainerInterface;

final class CitizenCustomCommands extends DrushCommands {
	public function __construct(private readonly Token $token) {
		parent::__construct();
	}

	/**
	* {@inheritdoc}
	*/
	public static function create(ContainerInterface $container) {
		return new static($container->get('token'));
	}

	// bulk process for profiles. sets campus to eau claire if no campus is selected, and publishes if field_active is true.
	#[CLI\Command(name: 'citizen_custom:publish_profiles')]
	public function activate_profiles(){
		// get all bios nodes (bios = profiles)
		$nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
			'type'=>'bios',
		]);

		$campus_count = 0;
		$publish_count = 0;

		foreach($nodes as $node){
			$nid = $node->id();

			// if they have no campus, give them Eau Claire
			if($node->get('field_campus')->isEmpty() || $node->get('field_campus')->getString() == '0'){
				// 12 = Eau Claire (campus)
				$node->set('field_campus', [12]);
				$node->save();

				$this->logger()->success('Node ('.$nid.') has no campus, so I just set it to Eau Claire.');
				$campus_count++;
			}

			// if they are unpublished, and field_active is true, publish them:
			$moderation_state = $node->get('moderation_state')->getString();
			if($moderation_state != 'published'){
				// if "field_active" is true, then let's publish this node.
				$active = boolval($node->get('field_active')->getString());
				if($active){
					$node->set('moderation_state', 'published');
					$node->save();

					$this->logger()->success('Node ('.$nid.') was not published, but is active, so I just published the node.');
					$publish_count++;
				}
			}
		}

		$this->logger()->success('Done! Assigned '.$campus_count.' campuses and published '.$publish_count.' profiles.');
	}

	#[CLI\Command(name: 'citizen_custom:default_show_email_phone')]
	public function default_show_email_phone(){
		// load all bios to set the default on
		$nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
			'type'=>'bios',
		]);

		$count = 0;
		foreach($nodes as $node){
			$needs_save = false;

			// if there is no value for field_show_email, set one
			if($node->get('field_show_email')->isEmpty()){
				$node->set('field_show_email', 1);
				$needs_save = true;
			}

			// same for field_show_phone
			if($node->get('field_show_phone')->isEmpty()){
				$node->set('field_show_phone', 1);
				$needs_save = true;
			}

			if($needs_save){
				echo 'Setting defaults on node ('.$node->id().')'."\n";
				$node->save();
				$count++;
			}
		}

		$this->logger()->success('Set defaults on '.$count.' profiles.');
	}

	#[CLI\Command(name: 'citizen_custom:delete_revisions')]
	public function delete_revisions($content_type){
		// validate content_type
		$content_types = \Drupal::entityTypeManager()->getStorage('node_type')->loadMultiple();
		$content_types = array_keys($content_types);
		if(!in_array($content_type, $content_types)){
			$this->output->writeln('Invalid content type "'.$content_type.'". Try one of these: '.implode(', ', $content_types));
			return;
		}

		$nodeStorage = \Drupal::entityTypeManager()->getStorage('node');

		// first load all nodes for the given content type
		$nodes = $nodeStorage->loadByProperties([
			'type'=>$content_type,
		]);

		// only remember the nids
		// we only want to load the node right before we use it,
		// to reduce the chance that outside edits happen while this is running.
		// I'm adding this after the fact because it's taking literally a week to run so it was not deleting the right revisions after awhile.
		$nids = [];
		foreach($nodes as $node){
			$nids[] = $node->id();
		}
		$total_node_count = count($nids);

		$this->io()->info('Found '.count($nodes).' "'.$content_type.'" nodes. I will now delete all non-current revisions on those.');

		// load all revisions,
		// and delete all of them, unless they're the default revision.
		$node_num = 0;
		foreach($nids as $nid){
			$node_delete_count = 0;
			$node_num++;

			$node = $nodeStorage->load($nid);
			$vids = $nodeStorage->revisionIds($node);
			foreach($vids as $vid){
				if($node->getLoadedRevisionId() != $vid){
					try{
						$nodeStorage->deleteRevision($vid);
						$node_delete_count++;
						$this->output->writeln('('.$node_num.'/'.$total_node_count.') Node '.$nid.': deleted revision '.$vid);
					}catch(\Exception $e){
						// tried to delete the default revision
						$this->io()->warning('I was about to delete the current revision of node '.$node->id().' but I did not.');
					}
				}
			}

			$this->output->writeln('--- Finished node '.$nid.'. Deleted '.$node_delete_count.' revisions from this node. ');
		}

		$this->io()->success('Done!');
	}
}
