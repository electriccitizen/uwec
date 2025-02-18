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
		$courseLeafSlug = $this->getCourseLeafSlug($program);
		if(empty($courseLeafSlug)){
			return ['#markup'=>''];
		}

		// grab the manually entered course numbers from the program
		$manualCourseNumbers = $this->getManualCourseNumbers($program);

		// get courses from CourseLeaf
		$courses = $this->getCourses($courseLeafSlug, $manualCourseNumbers);

		return [
			'#theme'=>'courses_block',
			'#program_title'=>$program->getTitle(),
			'#courseleaf_slug'=>$courseLeafSlug,
			'#courses'=>$courses,
		];
	}

	// returns the course leaf slug for the given program.
	// returns false if there isn't one.
	protected function getCourseLeafSlug($program){
		$courseLeafSlug = $program->get('field_courseleaf_slug')->getValue();
		if(empty($courseLeafSlug)) return false;
		$courseLeafSlug = $courseLeafSlug[0]['value'];
		if(empty($courseLeafSlug)) return false;
		return $courseLeafSlug;
	}

	// returns an array of manually entered course numbers.
	// the array will be empty if there are none.
	protected function getManualCourseNumbers($program){
		$nums = $program->get('field_courses')->getValue();
		if(empty($nums)) return [];

		// convert array of arrays with key "value" to useful array
		$nums = array_map(fn($arr) => $arr['value'], $nums);
		return $nums;
	}

	// returns an array of courses
	// each course is an array containing "title", "number", and "description"
	// the course numbers provided in $manualCourseNumbers will be returned first, if they exist in the response.
	// random courses will be selected if there are no more manual course numbers to choose from.
	protected function getCourses($slug, $manualCourseNumbers){
		$url = 'https://catalog.uwec.edu/ribbit/?page=getcourse.rjs&subject='.$slug; $response = \Drupal::httpClient()->request('GET', $url);

		// anything but a 200 is bunk
		if($response->getStatusCode() != 200){
			\Drupal::logger('citizen_custom')->error('Catalog API returned response code ('.$response->getStatusCode().') with body: ('.$response->getBody().')');
			return [];
		}

		$xml = new \SimpleXMLElement($response->getBody()->getContents());

		// make sure we have at least one course in the response
		if(empty($xml->course->count())){
			\Drupal::logger('citizen_custom')->error('Catalog API returned a response code of 200 but there are no courses after parsing the body with SimpleXMLElement. Body: ('.$response->getBody().')');
			return [];
		}

		// gather all courses in an array, indexed by lowercase course number
		$allCourses = [];
		for($i = 0; $i < $xml->course->count(); $i++){
			$course = $xml->course[$i];
			$courseHtml = new \DOMDocument();
			$courseHtml->loadHTML((string)$course);
			$courseDiv = $courseHtml->documentElement->firstChild->firstChild;

			$courseData = [];
			$courseData['number'] = (string)$course['code'];

			// validate subject
			// this seems unecessary because we are already passing it to the api request as a filter,
			// however the courseleaf api has a bug,
			// where asking for subject "PH" returns "PH" courses
			// but also "PHIL" and "PHYS" courses.
			if(strtok($courseData['number'], ' ') != $slug) continue;

			// default title and description, in case the search below fails
			$courseData['title'] = $courseData['number'];
			$courseData['description'] = '';

			// search through children nodes to try to find the title and description
			foreach($courseDiv->childNodes as $child){
				if($child->nodeType == XML_ELEMENT_NODE){
					$childClass = $child->getAttribute('class');
					if($childClass == 'courseblocktitle'){
						if(!empty($child->firstChild) && !empty($child->firstChild->firstChild)){
							$courseData['title'] = $this->sanitizeTitle($child->firstChild->firstChild->data, $courseData['number']);
						}
					}elseif($childClass == 'courseblockdescription'){
						if(!empty($child->firstChild)){
							$courseData['description'] = $this->sanitizeDescription($child->firstChild->data);
						}
					}
				}
			}

			$allCourses[strtolower($courseData['number'])] = $courseData;
		}

		// make sure we at least grabbed one course to use
		if(empty($allCourses)){
			\Drupal::logger('citizen_custom')->error('There were courses in the Catalog API response, but after gathering courses into $allCourses, there are none.');
			return [];
		}

		// ok now we try to pick the right 3 courses to show.
		$returnedCourses = [];

		// starting with the manually selected ones.
		foreach($manualCourseNumbers as $courseNum){
			$manualKey = strtolower($courseNum);
			if(isset($allCourses[$manualKey])){
				$returnedCourses[] = $allCourses[$manualKey];

				// remove this course from the array of all courses
				// so if we do a random selection later, this course cannot be chosen again.
				unset($allCourses[$manualKey]);

				// we only need 3
				if(count($returnedCourses) >= 3) break;
			}
		}

		// if we don't have 3 yet, pick some random ones:
		if(count($returnedCourses) < 3){
			$numCoursesToPick = 3 - count($returnedCourses);
			$randomKeys = array_rand($allCourses, $numCoursesToPick);
			if(is_array($randomKeys)){
				foreach($randomKeys as $key){
					$returnedCourses[] = $allCourses[$key];
				}
			}else{
				// array_rand returns the key itself if $numCoursesToPick is 1.
				$returnedCourses[] = $allCourses[$randomKeys];
			}
		}

		return $returnedCourses;
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

	// returns a sanitized version of the given $description.
	protected function sanitizeDescription($description){
		// trim whitespace
		$description = trim($description);

		// i don't exactly follow why this works but it fixes a weird smart quote situation in bcom 309.
		return mb_convert_encoding($description, 'ISO-8859-1', 'UTF-8');
	}
}
