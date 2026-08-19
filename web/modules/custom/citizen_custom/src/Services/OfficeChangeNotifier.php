<?php
namespace Drupal\citizen_custom\Services;

class OfficeChangeNotifier{
	// this will send the notification if this entity
	// is in such a state where the notification is relevant.
	public function maybeSend($entity){
		// we only care about profiles (bios)
		if($entity->bundle() !== 'bios') return;

		// we don't care about newly created ones
		if($entity->isNew()) return;

		// we only care if this entity was just updated
		if(!isset($entity->original)) return;

		// we only care if the old office value is different than the new one.
		if($entity->get('field_office')->equals($entity->original->get('field_office'))) return;

		// we need this pantheon function for it to work
		// this also skips it on local env
		if(!function_exists('pantheon_get_secret')) return;

		$this->send($entity);
	}

	// attempts to send the email notification for the given profile
	protected function send($profile){
		$from = "web@uwec.edu";
		$url = "https://graph.microsoft.com/v1.0/users/$from/sendMail";

		// determine who gets the email based on which env we're on
		$to_emails = [];
		$env = '';
		if(isset($_ENV['PANTHEON_ENVIRONMENT'])){
			if($_ENV['PANTHEON_ENVIRONMENT'] == 'live'){
				$env = 'live';
				$to_emails = [
					'larsomat@uwec.edu',
					'ernstcs@uwec.edu',
					'browersm@uwec.edu',
					'hakesbr@uwec.edu',
					'hansonbj@uwec.edu',
					'egelande@uwec.edu',
					'garveyn@uwec.edu',
					'stevenej@uwec.edu',
					'sotkast@uwec.edu',
				];
			}else{
				$env = $_ENV['PANTHEON_ENVIRONMENT'];
				$to_emails = ['larsomat@uwec.edu'];
			}
		}else{
			// only send to a developer on any other env
			$env = 'local';
			$to_emails = ['larsomat@uwec.edu'];
		}

		// convert normal array of "to" addresses
		// to the jank microsoft-y format
		$to = [];
		foreach($to_emails as $email){
			$to[] = [
				'emailAddress'=>[
					'address'=>$email,
				]
			];
		}

		// generate email body
		$body = '';
		if($env == 'live'){
			$body .= '<h1>Office changed</h1>';
		}else{
			$body .= '<h1>Office changed (on '.$env.' env)</h1>';
		}

		$body .= '<p>'.$profile->field_first_name->getString().' '.$profile->field_last_name->getString().' ('.$profile->field_username->getString().')';
		$body .= ' just updated their office on <a href="https://www.uwec.edu/profiles/'.$profile->field_username->getString().'">their public profile</a>.</p>';

		$body .= "<h2>Old Office</h2>";
		$body .= '<p>'.$profile->original->get('field_office')->getString().'</p>';

		$body .= "<h2>New Office</h2>";
		$body .= '<p>'.$profile->field_office->getString().'</p>';

		$http = \Drupal::httpClient();
		try{
			$response = $http->post($url, [
				'headers'=>[
					'Authorization'=>'Bearer '.$this->getAccessToken(),
					'Content-Type'=>'application/json',
				],
				'json'=>[
					'message' => [
						'subject'=>'www profile office update',
						'body'=>[
							'contentType'=>'HTML',
							'content'=>$body,
						],
						"toRecipients"=>$to,
					],
					"saveToSentItems"=>"true",
				],
			]);
			if($response->getStatusCode() != 202){
				\Drupal::logger('citizen_custom')->error('Failed to send office change notification. I got an access token, but sending the mail returned the status: "'.$response->getStatusCode().'"');
			}
		}catch(\Exception $e){
			\Drupal::logger('citizen_custom')->error($e->getMessage());
		}
	}

	// fetches and returns an access token from microsoft
	protected function getAccessToken(){
		$tenant_id = 'dd068b97-7593-4938-8b32-14faef2af1d8';
		$client_id = 'ea0bb0dd-3029-4256-aa56-15c94718099d';
		$client_secret = pantheon_get_secret('graph_api_client_secret');

		$url = "https://login.microsoftonline.com/$tenant_id/oauth2/v2.0/token";
		$data = [
			'grant_type' => 'client_credentials',
			'client_id' => $client_id,
			'client_secret' => $client_secret,
			'scope' => 'https://graph.microsoft.com/.default',
		];

		$http = \Drupal::httpClient();
		$response = $http->post($url, [
			'form_params'=>$data,
		]);
		$body = json_decode($response->getBody(), true);

		if(empty($body['access_token'])){
			throw new \Exception('Unable to get an "access_token" to send office change notification.');
		}
		return $body['access_token'];
	}
}
