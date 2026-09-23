<?php
namespace Drupal\uwec_reports\Controller;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Url;

class ManualProfiles extends ControllerBase{
	public function index(){
		return [
			'#type'=>'container',
			'intro'=>[
				'#type'=>'markup',
				'#markup'=>'<p>A list of all Profiles that have their "automatic" toggle disabled. These profiles will <em>not</em> be automatically unpublished when the person leaves.</p>',
			],
			'campus_id_p'=>[
				'#type'=>'markup',
				'#markup'=>'<h2>Campus ID</h2><p>Campus ID is shown here because Campus ID is how the data feed identifies Profiles and Users. So without a Campus ID, it must be a manual profile.</p>',
			],
			'table'=>$this->getTableRenderArray(),
		];
	}

	// loads results and returns a render array to display the results in a table
	function getTableRenderArray(){
		$node_storage = \Drupal::entityTypeManager()->getStorage('node');
		$nids = $node_storage->getQuery()
			->condition('type', 'bios')
			->condition('field_active', false)
			->accessCheck(false)
			->execute();

		$header = [
			'nid'=>'Node ID',
			'name'=>'Name',
			'email'=>'Email',
			'status'=>'Status',
			'campus_id'=>'Campus ID',
			'actions'=>'Actions',
		];

		$manual_profiles_url = Url::fromRoute('uwec_reports.manual_profiles')->toString();
		$profiles = $node_storage->loadMultiple($nids);
		$rows = [];
		foreach($profiles as $profile){
			$status_text = 'Unpublished';
			$status_class = '';
			if($profile->status->value){
				$status_text = 'Published';
				$status_class = 'marker--published';
			}

			$rows[] = [
				'nid'=>$profile->nid->value,
				'name'=>$profile->field_first_name->value.' '.$profile->field_last_name->value,
				'email'=>$profile->field_email->value,
				'status'=>[
					'data'=>[
						'#type'=>'markup',
						'#markup'=>'<span class="views-field"><span class="marker '.$status_class.'">'.$status_text.'</span></span>',
					]
				],
				'campus_id'=>$profile->field_campus_id->value,
				'actions'=>[
					'data'=>[
						'#type'=>'markup',
						'#markup'=>'
							<a href="'.Url::fromRoute('entity.node.edit_form', ['node' => $profile->nid->value])->toString().'">edit</a>
							<a href="'.Url::fromRoute('entity.node.delete_form', ['node' => $profile->nid->value], ['query'=>['destination'=>$manual_profiles_url]])->toString().'">delete</a>
						',
					],
				],
			];
		}

		return [
			'#type'=>'table',
			'#header'=>$header,
			'#rows'=>$rows,
			'#empty'=>'There are currently no manual profiles.',
		];
	}
}