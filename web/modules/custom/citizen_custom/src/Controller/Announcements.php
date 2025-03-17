<?php
namespace Drupal\citizen_custom\Controller;
use Symfony\Component\HttpFoundation\Response;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Datetime\DrupalDateTime;

class Announcements extends ControllerBase{
	// the public ajax endpoint for retrieving announcements for a given page.
	public function ajax(){
		$path = \Drupal::request()->query->get('path');

		// I'm returning a symfony Response because I don't want the whole page layout
		$response = new Response();

		// if we have a path, load the announcements to show,
		// and render the ajax template
		if(!empty($path)){
			$build = [
				'#theme'=>'announcements_ajax',
				'#nids'=>$this->getCurrentAnnouncementNids($path),
			];
			$html = \Drupal::service('renderer')->renderRoot($build);
			$response->setContent($html);
		}

		// set noindex header
		$response->headers->set('X-Robots-Tag','noindex');

		return $response;
	}

	// returns an array of announcement node ids (nids) to display
	// on the given page $path,
	// and for the current datetime.
	private function getCurrentAnnouncementNids($path){
		$query = \Drupal::entityQuery('node');

    $query->accessCheck(FALSE);

		// get a current timestamp in the right timezone and format for an entity query
		$now = new DrupalDateTime('now');
		// shame upon the Smart Date Range devs for storing the datetime values in the default timezone instead of GMT
		//$now->setTimezone(new \DateTimeZone(DateTimeItemInterface::STORAGE_TIMEZONE));
		// double shame upon Smart Date Range devs for using unix timestamps instead of the drupal datetime storage format
		//$now = $now->format(DateTimeItemInterface::DATETIME_STORAGE_FORMAT);
		$now = $now->format('U');

		$date_range_condition = $query->andConditionGroup()
			->condition('field_dates.value', $now, '<=')
			->condition('field_dates.end_value', $now, '>=');

		// now we create a section_url condition
		// first, generate a list of "sections" the current page is in
		// in other words, the full path to all parent pages
		$sections = ['/'];
		$section_path = '/';
		$current_uri_tokens = array_filter(explode('/', $path));
		foreach($current_uri_tokens as $path_token){
			$section_path .= $path_token.'/';
			$sections[] = $section_path;
		}

		$section_url_condition = $query->orConditionGroup()
			->condition('field_section_url', $sections, 'in')
			->notExists('field_section_url');
		
		$query->condition('type', 'announcement')
			->condition('status', 1)
			->condition($date_range_condition)
			->condition($section_url_condition)
			->sort('field_dates.value', 'DESC');

		// the query returns an array with integer keys
		// however, you can't put integer keys into a render array, I guess?
		// even though that doesn't really make sense because after doing this, it still has integer keys.
		// but anyways, this prevents an error where it yells about invalid render array keys.
		// (the keys we're getting rid of are the revision IDs)
		return array_values($query->execute());
	}
}