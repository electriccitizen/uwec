<?php
namespace Drupal\livewhale;

class API{
	const BASE_URL = 'https://calendar.uwec.edu/live/json/v2/';
	private $httpClient;

	public function __construct(){
		$this->httpClient = \Drupal::httpClient();
	}

	// returns an array of all groups
	// each group is an array with keys "id", "title", and a few other fields
	public function getGroups(){
		return $this->getNonPaginatedResults('groups');
	}

	// ignores livewhale pagination metadata and just returns the good stuff from the api response as an array.
	protected function getNonPaginatedResults($path){
		$url = static::BASE_URL.$path;
		$response = $this->httpClient->request('GET', $url);

		// make sure it's a 200
		if($response->getStatusCode() !== 200){
			\Drupal::logger('livewhale')->error('The Livewhale API request to "'.$url.'" returned response code ('.$response->getStatusCode().') with body: ('.$response->getBody()->getContents().')');
			return [];
		}

		$body = json_decode($response->getBody()->getContents(), true);

		// make sure there is at least some data.
		if(empty($body['data'])){
			\Drupal::logger('livewhale')->error('The LiveWhale API request to "'.$url.'" returned a 200 but there are no results in the data.');
			return [];
		}

		return $body['data'];
	}
}