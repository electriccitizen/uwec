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
			if($node->get('field_campus')->isEmpty()){
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

}
