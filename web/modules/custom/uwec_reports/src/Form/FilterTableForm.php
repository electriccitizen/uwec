<?php
namespace Drupal\uwec_reports\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class FilterTableForm extends FormBase{
	public function getFormId(){
		return 'uwec_reports_filters';
	}

	// $filters should be an array of render array fields
	// $filters gets passed in through the call to formBuilder()->getForm()
	// see Controller\TestimonialsController::getFilters() for an example.
	public function buildForm(array $form, FormStateInterface $form_state, $filters=[]){
		foreach($filters as $field_name=>$filter){
			$form[$field_name] = $filter['render'];
		}

		$form['actions']['#type'] = 'actions';
		$form['actions']['submit'] = [
			'#type'=>'submit',
			'#value'=>$this->t('Filter'),
			'#button_type'=>'primary',
		];

		// never cache filter forms
		$form['#cache'] = ['max-age' => 0];

		return $form;
	}

	public function submitForm(array &$form, FormStateInterface $form_state){
		$form_state->disableRedirect();
	}
}