<?php
namespace Drupal\citizen_custom\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Datetime\DrupalDateTime;

/**
 * Provides the "Announcements" block.
 *
 * @Block(
 *   id = "announcements",
 *   admin_label = @Translation("Announcements"),
 *   category = @Translation("Announcements"),
 * )
 */
class Announcements extends BlockBase{
	/**
	 * {@inheritdoc}
	 */
	public function build(){
		// don't render anything if we're in an admin context
		// this will prevent sitewide announcements from cluttering the admin pages
		if(\Drupal::service('router.admin_context')->isAdminRoute()){
			// TODO add cache here once it's figured out
			return [
				'#plain_text'=>'',
			];
		}

		return [
			'#theme'=>'announcements_block',
			'#cache'=>[
				// TODO this does not work, and I'm not sure why.
				'contexts'=>['url.path'],
			],
			'#nids'=>$this->getCurrentAnnouncementNids(),
		];
	}

	// returns an array of announcement node ids (nids) to display
	// on the current page, for the current datetime.
	protected function getCurrentAnnouncementNids(){
		$query = \Drupal::entityQuery('node');

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
		$sections = [];
		$section_path = '/';
		$current_uri_tokens = array_filter(explode('/', \Drupal::request()->getRequestUri()));
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
			->sort('field_dates.value', 'DESC')
			->accessCheck(false);

		// the query returns an array with integer keys
		// however, you can't put integer keys into a render array, I guess?
		// even though that doesn't really make sense because after doing this, it still has integer keys.
		// but anyways, this prevents an error where it yells about invalid render array keys.
		// (the keys we're getting rid of are the revision IDs)
		return array_values($query->execute());
	}
}