<?php
namespace Drupal\uwec_reports\Controller;
use Drupal\Core\Controller\ControllerBase;

class IndexController extends ControllerBase{
	public function show(){
		return ['#markup'=>'UWEC Reports!'];
	}
}