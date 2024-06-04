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
			new TwigFilter('phone', [$this, 'phone']),
		];
	}

	public function getFunctions(){
		return [
			new TwigFunction('indefinite_article', [$this, 'indefinite_article']),
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

	// formats a phone number to be displayed to the user
	public function phone($rawPhone){
		// we only care about the numeric characters
		// this will allow this filter to work no matter what the input format is.
		$num = preg_replace("/[^0-9]/", "", $rawPhone);

		// 123-123-1234
		if(strlen($num) == 10){
			return substr($num, 0, 3).'-'.substr($num, 3, 3).'-'.substr($num, 6);
		}

		// 123-1234
		if(strlen($num) == 7){
			return substr($num, 0, 3).'-'.substr($num, 3);
		}

		// if we got a weird number of numbers, just return them for display
		return $num;
	}

	// returns either "a" or "an" based on the following, given, noun.
	// this does *not* return the given noun.
	public function indefinite_article($noun){
		// just default to "a" if we have nothing to go on.
		if(empty($noun)) return 'a';

		// grab the first character, lowercased
		$first = strtolower(substr(trim($noun), 0, 1));

		// if it's a vowel, we want "an"
		if(in_array($first, ['a', 'e', 'i', 'o', 'u'])) return 'an';

		// otherwise just assume "a"
		return 'a';
	}
}