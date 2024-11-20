<?php
namespace Drupal\uwec_reports\Controller;
use Drupal\uwec_reports\FilterTableBase;

class TestimonialsController extends FilterTableBase{
	protected function getNodeType(){
		return 'testimonial';
	}

	protected function getCols(){
		return [
			'title'=>[
				'sort'=>true,
			],
			'body'=>[
				'label'=>'Quote',
				'sort'=>true,
			],
			'field_date'=>[
				'label'=>'Add Date',
				'sort'=>true,
				'render'=>function($node){
					$date = $node->field_date->first()->getValue()['value'];
					$time = strtotime($date);
					return date('m/d/Y', $time);
				},
			],
			'status'=>[
				'render'=>function($node){
					if($node->status->getString() == '1'){
						return 'published';
					}
					return 'archived';
				},
			],
			'author'=>[],
			'image'=>[
				'label'=>'Has Image?',
				'render'=>function($node){
					if($node->field_image->count() > 0){
						return 'yes';
					}
					return 'no';
				},
			],
			'actions'=>[],
		];
	}

	protected function getFilters(){
		return [
			'field_date'=>[
				'render'=>[
					'#type'=>'date',
					'#title'=>'Add Date',
				],
				'query'=>function($query, $value){
					$query->condition('field_date', $value, '>=');
				},
			],
		];
	}
}