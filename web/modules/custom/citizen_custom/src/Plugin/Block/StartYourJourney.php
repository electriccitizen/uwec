<?php
namespace Drupal\citizen_custom\Plugin\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Url;

/**
 * Provides the "Start Your Blugold Journey" block.
 *
 * @Block(
 *   id = "start_journey",
 *   admin_label = @Translation("Start Your Journey"),
 *   category = @Translation("Custom"),
 * )
 */
class StartYourJourney extends BlockBase {
	/**
	 * {@inheritdoc}
	 *
	 * This block only outputs an empty div.
	 * The existence of this empty div will trigger an AJAX request to /ajax/announcements on page load that fills it with relevant announcements.
	 * I did it this way primarily to work around the cache, because it's ok if the empty div gets cached.
	 */
	public function build() {
		return [
			'#theme' => 'start_your_journey_block',
      '#title' => $this->t('Start Your Blugold Journey'),
      '#links' => [
        [
          'text' => $this->t('Visit'),
          'url' => Url::fromRoute('entity.node.canonical', ['node' => 8068])->toString(),
        ],
        [
          'text' => $this->t('Apply'),
          'url' => Url::fromRoute('entity.node.canonical', ['node' => 7696])->toString(),
        ]
      ]
		];
	}
}
