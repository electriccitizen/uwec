<?php
namespace Drupal\citizen_custom\Plugin\Block;
use Drupal\Core\Block\BlockBase;

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
	 *
	 * This block only outputs an empty div.
	 * The existence of this empty div will trigger an AJAX request to /ajax/announcements on page load that fills it with relevant announcements.
	 * I did it this way primarily to work around the cache, because it's ok if the empty div gets cached.
	 */
	public function build(){
		return [
			'#theme'=>'announcements_block',
		];
	}
}