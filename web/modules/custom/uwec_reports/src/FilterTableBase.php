<?php
namespace Drupal\uwec_reports;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;

// a base controller for uwec reports with a filter form and table
// see Controller\TestimonialsController for an example on how to use this class.
abstract class FilterTableBase extends ControllerBase{
	// should return the node type machine name to load.
	protected abstract function getNodeType();

	protected abstract function getFilters();

	// should return an array of columns for the table.
	// each array should have a top-level key that uniquely represents that column.
	// 
	// each array can have a "label" key which is the user-viewable string for that column header.
	// if no "label" is provided, the id is ucwords() to create it.
	// 
	// if the column should be sortable, then add a "field" key with the name of the field to sort on.
	// 
	// each array can have a "value" key, which should be a function that accepts the $node and returns a string value or render array.
	// if no "value" key exists, it attempts to do $node->"field"->getString().
	// this means you have to specify either a "field" or "value"
	protected abstract function getCols();

	// returns the render array for this report.
	// your route should point at this function.
	public function show(){
		return [
			'filters'=>$this->renderFilters(),
			'table'=>$this->renderTable(),
			'pager'=>$this->renderPager(),
		];
	}

	protected function renderFilters(){
		$filters = $this->getFilters();
		if(!empty($filters)){
			return \Drupal::formBuilder()->getForm('Drupal\uwec_reports\Form\FilterTableForm', $filters);
		}
	}

	protected function renderTable(){
		$header = [];
		foreach($this->getCols() as $col=>$def){
			// if $col is an int, that means only a single string was specified
			// which should be a field name on the node.
			$this_header = [
				'data'=>$def['label'] ?? ucwords($col),
			];
			if(!empty($def['sort'])) $this_header['field'] = $col;
			$header[$col] = $this_header;
		}

		$rows = [];
		foreach($this->getNodes() as $node){
			$rows[] = $this->renderRow($node);
		}

		return [
			'#type'=>'table',
			'#header'=>$header,
			'#rows'=>$rows,
			'#empty'=>'No nodes to show.',
		];
	}

	protected function renderPager(){
		return ['#type'=>'pager'];
	}

	// returns an array of Nodes to display
	protected function getNodes(){
		$query = \Drupal::entityQuery('node')
			->condition('type', $this->getNodeType())
			->accessCheck(false);

		// apply filters as query conditions
		if(!empty($_POST)){
			foreach($this->getFilters() as $field_name => $filter){
				if(!empty($_POST[$field_name])){
					$val = $_POST[$field_name];
					if(isset($filter['query'])){
						// if this filter provides a query function, use it
						$filter['query']($query, $val);
					}else{
						// otherwise, just assume a simple = condition
						$query->condition($field_name, $val);
					}
				}
			}
		}
		
		$nids = $query->execute();
		return \Drupal\Node\Entity\Node::loadMultiple($nids);
	}

	// returns an array that represents a single node to be displayed on a table.
	// the keys of the returned array need to match the table header keys.
	// the values can be strings or render arrays
	protected function renderRow($node){
		$row = [];
		foreach($this->getCols() as $col=>$def){
			$row[$col] = ['data'=>$this->renderCell($node, $col)];
		}
		return $row;
	}

	// returns a string or render array for a given field on the given node
	protected function renderCell($node, $fieldName){
		// render author field
		if($fieldName == 'author'){
			$author_user_id = $node->getOwnerId();
			$user = \Drupal\user\Entity\User::load($author_user_id);
			if($user){
				return $user->getDisplayName();
			}
			return '';
		}

		// render actions
		if($fieldName == 'actions'){
			return [
				'#type'=>'dropbutton',
				'#dropbutton_type'=>'small',
				'#links'=>[
					'edit'=>[
						'title'=>'Edit',
						'url'=>Url::fromRoute('entity.node.edit_form', ['node'=>$node->id()])->toString(),
					],
					'delete'=>[
						'title'=>'Delete',
						'url'=>'/node/'.$node->id().'/delete',
					],
				],
			];
		}

		$def = $this->getCols()[$fieldName];

		// render with column-specific render function
		if(isset($def['render'])) return $def['render']($node);

		// try to get value from node
		$fieldObj = $node->$fieldName;
		if($fieldObj){
			return $fieldObj->getString();
		}

		// nothing worked so just render nothing
		return '';
	}
}
