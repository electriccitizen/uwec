<?php
namespace Drupal\citizen_custom\Plugin\Block;
use Drupal\Core\Block\BlockBase;

/**
 * Provides the "Courses" block.
 *
 * @Block(
 *   id = "courses",
 *   admin_label = @Translation("Courses"),
 *   category = @Translation("Courses"),
 * )
 */
class Courses extends BlockBase{
	/**
	 * {@inheritdoc}
	 */
	public function build(){
		$program = \Drupal::routeMatch()->getParameter('node');

		// if there is no program, render nothing
		if($program === null){
			return ['#markup'=>''];
		}

		// grab the courseleaf slug
		$courseLeafSlug = $program->get('field_courseleaf_slug')->getValue();

		// if this program doesn't have a CourseLeaf Slug, there is nothing we can do.
		if(empty($courseLeafSlug)){
			return ['#markup'=>''];
		}

		// there can only ever be one, so just grab that to simplify
		$courseLeafSlug = $courseLeafSlug[0]['value'];

		// get courses from CourseLeaf
		$courses = $this->getCourses($courseLeafSlug);

		return [
			'#theme'=>'courses_block',
			'#program_title'=>$program->getTitle(),
			'#courseleaf_slug'=>$courseLeafSlug,
			'#courses'=>$courses,
		];
	}

	// returns an array of courses
	// each course is an array containing "title", "number", and "description"
	protected function getCourses($slug){
		$url = 'https://catalog.uwec.edu/ribbit/?page=getcourse.rjs&subject='.$slug; $response = \Drupal::httpClient()->request('GET', $url);

		// anything but a 200 is bunk
		if($response->getStatusCode() != 200){
			return [];
		}

		$courses = [];
		$xml = new \SimpleXMLElement($response->getBody()->getContents());

		// pick 3 random courses to parse
		$random_indices = $this->getRandomIndices($xml->course->count());

		foreach($random_indices as $course_idx){
			$course = $xml->course[$course_idx];
			$courseHtml = new \DOMDocument();
			$courseHtml->loadHTML((string)$course);
			$courseDiv = $courseHtml->documentElement->firstChild->firstChild;

			$courseData = [];
			$courseData['number'] = (string)$course['code'];

			foreach($courseDiv->childNodes as $child){
				if($child->nodeType == XML_ELEMENT_NODE){
					$childClass = $child->getAttribute('class');
					if($childClass == 'courseblocktitle'){
						$courseData['title'] = $this->sanitizeTitle($child->firstChild->firstChild->data, $courseData['number']);
					}elseif($childClass == 'courseblockdescription'){
						$courseData['description'] = trim($child->firstChild->data);
					}
				}
			}
			$courses[] = $courseData;
		}

		return $courses;
	}

	// returns 3 random indices with the given $max
	// for example a max of 15 could return [0, 10, 14]
	// if $max is 0, 1, or 2, this will return just the minimums, for example 2 will return [0, 1]
	protected function getRandomIndices($max){
		if($max == 0) return [];

		$indices = array_fill(0, $max, true);
		
		if($max > 3){
			return array_rand($indices, 3);
		}else{
			return array_keys($indices);
		}
	}

	// takes the contents of a "p.courseblocktitle" tag and returns a sanitized title string
	protected function sanitizeTitle($title, $courseNumber){
		// remove non-breaking space characters
		$title = str_replace("\u{A0}", ' ', $title);

		// remove the course number
		if(!empty($courseNumber)){
			$title = str_replace($courseNumber, '', $title);
		}

		// remove everything after the first (
		$firstParen = strpos($title, '(');
		if($firstParen !== false){
			$title = substr($title, 0, $firstParen);
		}

		// remove extra whitespace
		$title = trim($title);

		return $title;
	}
}
