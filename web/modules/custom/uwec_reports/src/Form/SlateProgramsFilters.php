<?php
namespace Drupal\uwec_reports\Form;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;

class SlateProgramsFilters extends FormBase{
  public function getFormId(): string {
    return 'uwec_reports_slate_programs_filters';
  }

  public function buildForm(array $form, FormStateInterface $form_state): array {
    $request = $this->getRequest();

    // Use GET so filter values appear in the URL query string.
    $form['#method'] = 'get';
    $form['#after_build'][] = [$this, 'afterBuild'];

    // Disable token and build_id to keep query URLs clean.
    $form['#token'] = FALSE;

    $form['filters'] = [
      '#type' => 'container',
      '#attributes' => ['class' => ['container-inline']],
    ];

    $form['filters']['more_info'] = [
      '#type'=>'select',
      '#title'=>$this->t('Get More Info'),
      '#options'=>[
        ''=>$this->t('- Any -'),
        'on'=>$this->t('On'),
        'off'=>$this->t('Off'),
      ],
      '#default_value'=>$request->query->get('more_info', ''),
    ];

    $form['filters']['degree_level'] = [
      '#type' => 'select',
      '#title' => $this->t('Degree Level'),
      '#options' => [
        '' => $this->t('- Any -'),
        'graduate' => $this->t('Graduate'),
        'undergraduate' => $this->t('Undergraduate'),
      ],
      '#default_value' => $request->query->get('degree_level', ''),
    ];

    $form['filters']['actions'] = [
      '#type' => 'actions',
    ];

    $form['filters']['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Filter'),
      // Prevent Drupal from adding ?op=Filter to the URL.
      '#name' => '',
    ];

    // Add a Reset link if filters are currently active.
    if($request->query->has('degree_level') || $request->query->has('more_info')){
      $form['filters']['actions']['reset'] = [
        '#type' => 'link',
        '#title' => $this->t('Reset'),
        '#url' => \Drupal\Core\Url::fromRoute('<current>'),
        '#attributes' => ['class' => ['button']],
      ];
    }

    return $form;
  }

  public function afterBuild(array $form, FormStateInterface $form_state) {
    // remove a bunch of url junk
    unset(
      $form['form_build_id'],
      $form['form_id'],
      $form['form_token']
    );
    return $form;
  }

  // they shouldn't have made this method abstract
  public function submitForm(array &$form, FormStateInterface $form_state): void {}
}