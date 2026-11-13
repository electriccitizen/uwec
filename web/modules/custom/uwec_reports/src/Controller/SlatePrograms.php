<?php
namespace Drupal\uwec_reports\Controller;
use Drupal\Core\Controller\ControllerBase;

class SlatePrograms extends ControllerBase{
	public function index(){
		$form = $this->formBuilder()->getForm('\Drupal\uwec_reports\Form\SlateProgramsFilters');

		return [
			'#type'=>'container',
			'intro'=>[
				'#type'=>'markup',
				'#markup'=>'<p>Program pages display two different Slate forms, depending on the Degree Level: one for Graduate, and another for Undergraduate.</p>',
			],
			$form,
			'table'=>$this->getTable(),
		];
	}

	// loads results and returns a render array to display the results in a table
	function getTable(){
		$request = \Drupal::request();

		$storage = \Drupal::entityTypeManager()->getStorage('node');
		$query = $storage->getQuery()
			->condition('type', 'academic_program')
			->sort('title')
			->accessCheck(false);

		// optionally filter by degree level (grad/undergrad)
		$degree_level = $request->get('degree_level');
		if(!empty($degree_level)){
			$query->condition('field_degree_level', $degree_level);
		}

		// optionally filter on "Get More Info" flag
		if($request->query->has('more_info')){
			$more_info = $request->get('more_info');
			if($more_info == 'on'){
				$query->condition('field_request_for_info', '1');
			}else if($more_info == 'off'){
				$query->condition('field_request_for_info', '0');
			}
		}

		$nids = $query->execute();
		$programs = $storage->loadMultiple($nids);

		$header = [
			'nid'=>'Node ID',
			'title'=>'Title',
			'get_more_info'=>'Get More Info',
			'degree_level'=>'Degree Level',
			'actions'=>'Actions',
		];

		$rows = [];
		foreach($programs as $program){
			$more_info_class = '';
			$more_info_text = 'off';
			if($program->field_request_for_info->value){
				$more_info_class = 'marker--published';
				$more_info_text = 'on';
			}

			$rows[] = [
				'nid'=>$program->nid->value,
				'title'=>$program->getTitle(),
				'get_more_info'=>[
					'data'=>[
						'#type'=>'markup',
						'#markup'=>'<span class="views-field"><span class="marker '.$more_info_class.'">'.$more_info_text.'</span></span>',
					],
				],
				'degree_level'=>$program->field_degree_level->value,
				'actions'=>[
					'data'=>[
						'#type'=>'markup',
						'#markup'=>'
							<a href="'.$program->toUrl('edit-form')->toString().'">edit</a>
							<a href="'.$program->toUrl('delete-form')->toString().'">delete</a>
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
