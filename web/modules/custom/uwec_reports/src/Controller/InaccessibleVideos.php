<?php
namespace Drupal\uwec_reports\Controller;
use Drupal\Core\Controller\ControllerBase;

class InaccessibleVideos extends ControllerBase{
	public function index(){
		return [
			'#type'=>'container',
			'intro'=>[
				'#type'=>'markup',
				'#markup'=>'<p>A list of all Remote Videos with no audio description version.</p>',
			],
			'table'=>$this->getTableRenderArray(),
		];
	}

	function getTableRenderArray(){
		$storage = \Drupal::entityTypeManager()->getStorage('media');
		$mids = $storage->getQuery()
			->condition('bundle', 'remote_video')
			->notExists('field_audio_description_url')
			->accessCheck(false)
			->execute();

		$header = [
			'mid'=>'Media ID',
			'name'=>'Name',
			'actions'=>'Actions',
		];

		$videos = $storage->loadMultiple($mids);
		$rows = [];
		foreach($videos as $video){
			$rows[] = [
				'mid'=>$video->mid->value,
				'name'=>$video->getName(),
				'actions'=>[
					'data'=>[
						'#type'=>'markup',
						'#markup'=>'
							<a href="'.$video->toUrl('edit-form')->toString().'">edit</a>
							<a href="'.$video->toUrl('delete-form')->toString().'">delete</a>
						',
					],
				],
			];
		}

		return [
			'#type'=>'table',
			'#header'=>$header,
			'#rows'=>$rows,
			'#empty'=>'There are currently no videos without an audio description URL.',
		];
	}
}
