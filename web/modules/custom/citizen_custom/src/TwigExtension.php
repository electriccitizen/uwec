<?php

namespace Drupal\citizen_custom;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigFunction;

/**
 * Custom twig functions.
 */
class TwigExtension extends AbstractExtension {

	public function getName() {
    return 'citizen_custom.twig_extension';
  }

	public function getFilters() {
	  return [
	    new TwigFilter('shuffle', [$this, 'shuffleArray']),
	  ];
	}
	public function shuffleArray($list) {
  	if (!is_array($list)) return $list;
	  $keys = array_keys($list);
	  shuffle($keys);
	  $random = array();
	  foreach ($keys as $key){
	  	$random[$key] = $list[$key];
	  }
	  return $random;
	}
}