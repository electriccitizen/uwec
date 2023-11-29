<?php
namespace Drupal\citizen_custom\Controller;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\Response;

class Omnilert extends ControllerBase{
	// the public ajax endpoint for retrieving omnilerts.
	public function ajax(){
		$omnilertResponse = \Drupal::httpClient()->request('GET', $this->getOmnilertUrl());

		$response = new Response();
		if($omnilertResponse->getStatusCode() == 200){
			$response->setContent($omnilertResponse->getBody()->getContents());
			return $response;
		}else{
			\Drupal::logger('citizen_custom')->error('HTTP request to Omnilert returned a HTTP status "'.$omnilertResponse->getStatusCode().'"');
			return $response;
		}
	}

	// returns the omnilert url string.
	// this is slightly different if we are on the "live" env.
	protected function getOmnilertUrl(){
		$url = 'https://widgets.omnilert.net/85e861c6e4cd862c095ea28c658e5411-';

		// add our live/testing id to the end of the url
		if(isset($_ENV['PANTHEON_ENVIRONMENT']) && $_ENV['PANTHEON_ENVIRONMENT'] == 'live'){
			$url .= '741'; // live
		}else{
			$url .= '1571'; // testing
		}

		// append timestamp so we don't get a cached version
		$url .= '?timestamp='.time();

		return $url;
	}
}
