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

	#[CLI\Command(name: 'citizen_custom:find_nav_cycles')]
	public function find_nav_cycles(){

		// recursively gets child links and flattens them into an array
		// to test for any link that gets added twice
		function getLinks($key, $link){
			$links = [];
			$links[$key] = $link->link->getTitle().' '.$link->link->getUrlObject()->toString();

			foreach($link->subtree as $childKey=>$child){
				if(isset($links[$childKey])){
					die('cycle found! '.$childKey.': '.$child->link->getMenuName());
				}
				$childLinks = getLinks($childKey, $child);
				$links = $links + $childLinks;
			}

			return $links;
		}

		$parameters = new \Drupal\Core\Menu\MenuTreeParameters();
		//$parameters->onlyEnabledLinks();

		// set active trail
		//$menu_active_trail = \Drupal::service('menu.active_trail')->getActiveTrailIds($menu_name);
		//$parameters->setActiveTrail($menu_active_trail);
		$tree = \Drupal::menuTree()->load('main', $parameters);

		$allLinks = [];
		foreach($tree as $key=>$link){
			$childLinks = getLinks($key, $link);
			$allLinks = $allLinks + $childLinks;
		}

		var_dump($allLinks);
	}

	#[CLI\Command(name: 'citizen_custom:default_emeriti')]
	public function default_emeriti(){
		// load all bios to set the default on
		$nodes = \Drupal::entityTypeManager()->getStorage('node')->loadByProperties([
			'type'=>'bios',
		]);

		$count = 0;
		foreach($nodes as $node){
			$needs_save = false;

			// if there is no value for field_show_email, set one
			if($node->get('field_emeriti_status')->isEmpty()){
				$node->set('field_emeriti_status', 'not_emeritus');
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

	#[CLI\Command(name: 'citizen_custom:missing_videos')]
	public function missing_videos(){
		// get the oembed resolver and fetcher
		$url_resolver = \Drupal::service('media.oembed.url_resolver');
		$resource_fetcher = \Drupal::service('media.oembed.resource_fetcher');

		// load all videos
		$videos = \Drupal::entityTypeManager()
			->getStorage('media')
			->loadByProperties(['bundle'=>'remote_video']);

		$good = 0;
		$bad_ids = [];
		foreach($videos as $video){
			$url = $video->field_media_oembed_video->getString();
			$resource_url = $url_resolver->getResourceUrl($url);

			try{
				$resource = $resource_fetcher->fetchResource($resource_url);
				$good += 1;
			}catch(\Exception $e){
				$bad_ids[] = $video->id();
			}

			// calling fetchResources hits youtube.com,
			// so let's play nice and try to avoid getting blocked.
			sleep(1);
		}

		$this->io()->info('There are '.$good.' good video records.');

		if(count($bad_ids) > 0){
			$this->io()->warning('There are '.count($bad_ids).' bad video records. Here are the bad IDs');
			foreach($bad_ids as $id){
				$this->io()->info($id);
			}
		}else{
			$this->io()->success('There are 0 bad video records!');
		}
	}

	#[CLI\Command(name: 'citizen_custom:unused_files')]
	public function unused_files(){
		// zero usages
		$noRefFiles = [];

		// >=1 usages, but to only bad entities.
		$badRefFiles = [];

		// >=1 usages, some of which are good, some of which are bad.
		$mixedRefFiles = [];

		// >=1 usages, all of which are good.
		$goodRefFiles = [];

		// keep track of how many files per entity type
		$entityTypeCounts = [];

		// get some services
		$entityManager = \Drupal::entityTypeManager();
		$usage = \Drupal::service('file.usage');

		// get all file ids
		$fids = $entityManager->getStorage('file')->getQuery()->accessCheck(false)->execute();
		foreach($fids as $fid){
			$file = \Drupal\file\Entity\File::load($fid);
			$u = $usage->listUsage($file);
			if(!empty($u)){
				$goodRefCount = 0;
				$badRefCount = 0;

				foreach($u as $usage_records){
					// figure out which entity refs are naughty or nice, ho ho ho!
					foreach($usage_records as $entity_type => $entity_ids_with_count){
						$entity_ids = array_keys($entity_ids_with_count);
						$storage = $entityManager->getStorage($entity_type);
						foreach($entity_ids as $entity_id){
							$entity = $storage->load($entity_id);
							if($entity){
								$goodRefCount++;
							}else{
								$badRefCount++;
							}
						}
					}
				}

				// add this fid to the correct list,
				// depending on the good and bad counts.
				if($goodRefCount == 0){
					if($badRefCount == 0){
						$noRefFiles[] = $fid;
					}else{
						$badRefFiles[] = $fid;
					}
				}else{
					if($badRefCount == 0){
						$goodRefFiles[] = $fid;
					}else{
						$mixedRefFiles[] = $fid;
					}
				}
			}else{
				// no usage at all
				$noRefFiles[] = $fid;
			}
		}

		$this->io()->success('Files summary: There are '.count($fids).' files. '.count($goodRefFiles).' good, '.count($mixedRefFiles).' mixed, '.count($badRefFiles).' bad, and '.count($noRefFiles).' unused files.');

		if(!empty($noRefFiles)){
			$this->io()->warning('These '.count($noRefFiles).' file(s) have no usage references at all.');
			foreach($noRefFiles as $fid){
				echo $fid.PHP_EOL;
			}
		}
		if(!empty($badRefFiles)){
			$this->io()->warning('These '.count($badRefFiles).' file(s) have some usage, but only to entities that no longer exist.');
			foreach($badRefFiles as $fid){
				echo $fid.PHP_EOL;
			}
		}
		if(!empty($mixedRefFiles)){
			$this->io()->warning('These '.count($mixedRefFiles).' file(s) have some good, and some bad usage info.');
			foreach($mixedRefFiles as $fid){
				echo $fid.PHP_EOL;
			}
		}
	}
}
