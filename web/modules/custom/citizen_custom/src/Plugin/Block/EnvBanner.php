<?php
namespace Drupal\citizen_custom\Plugin\Block;
use Drupal\Core\Block\BlockBase;

/**
 * Provides the "Env Banner" block.
 *
 * @Block(
 *   id = "env_banner",
 *   admin_label = @Translation("Env Banner"),
 *   category = @Translation("Custom"),
 * )
 */
class EnvBanner extends BlockBase{
	public function build(){
		if($this->shouldDisplay()){
			return [
				'#theme'=>'env_banner_block',
			];
		}
	}

	// returns true if this block should display.
	// it should only display on pantheon, (not local)
	// and only if the env is not "live" (for example "test" and "dev")
	private function shouldDisplay(){
		return isset($_ENV['PANTHEON_ENVIRONMENT']) && $_ENV['PANTHEON_ENVIRONMENT'] != 'live';
	}
}
