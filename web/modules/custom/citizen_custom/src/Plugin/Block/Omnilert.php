<?php
namespace Drupal\citizen_custom\Plugin\Block;
use Drupal\Core\Block\BlockBase;

/**
 * Provides the "Omnilert" block.
 *
 * @Block(
 *   id = "omnilert",
 *   admin_label = @Translation("Omnilert"),
 *   category = @Translation("Omnilert"),
 * )
 */
class Omnilert extends BlockBase{
	/**
	 * {@inheritdoc}
	 */
	public function build(){
		return [
			'#theme'=>'omnilert_block',
		];
	}
}