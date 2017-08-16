<?php

namespace RmpEnterprise\AkkrooApi;

class Client
{
	protected $client;
	protected $access_token;
	protected $access_token_expiry;

	public function __construct($baseURL = 'https://akkroo.com/api/', $client_options = array())
	{
		$this->client = new \GuzzleHttp\Client(
			array(
				'base_url' => 'https://akkroo.com/api/',
				'defaults' => array(
					'headers' => array(
						'Accept'       => 'application/vnd.akkroo-v1.1.5+json',
						'Content-Type' => 'application/vnd.akkroo-v1.1.5+json'
					)
				)
			)
		);
		$this->client = new \GuzzleHttp\Client(array_merge_recursive($default_options, $client_options));
	}

	public function auth($username, $client_credentials, $scope = 'PublicAPI')
	{
		$request = $this->client->createRequest('POST', 'auth');
		$request->setHeader('Authorization', 'Basic ' . $client_credentials);

		$request->setBody(
			\GuzzleHttp\Stream\Stream::factory(
				json_encode(
					array(
						'grant_type' => 'client_credentials',
						'username'   => $username,
						'scope'      => $scope
					)
				)
			)
		);

		$response = $this->client->send($request);
		$data     = $response->json();

		if (isset($data['access_token'])) {
			$this->setAccessToken($data['access_token']);
			return [
				'access_token' => $data['access_token'],
				'expires_in' => $data['expires_in']
			];
		}

		return false;
	}

	public function setAccessToken($accessToken) {
		$this->access_token = $accessToken;
	}

	protected function makeRequest($url, $method = 'GET', array $options = array()) {
		if (is_null($this->access_token)) {
			return null;
		}

		$query = null;
		if (isset($options['query'])) {
			$query = $options['query'];
			unset($options['query']);
		}

		$request = $this->client->createRequest($method, $url, $options);
		$request->setHeader('Authorization', 'Bearer ' . $this->access_token);

		if (!is_null($query)) {
			$q = $request->getQuery();
			foreach($query as $param => $value) {
				$q->set($param, implode(',', $value));
			}
		}

		$response = $this->client->send($request);
		return $response->json();
	}

	protected function request($url, array $fields = array())
	{
		return $this->makeRequest($url, 'GET', [
			'query' => $fields
		]);
	}

	protected function post($url, $body) {
		return $this->makeRequest($url, 'POST', [
			'body' => json_encode($body)
		]);
	}

	public function selftest()
	{
		$response = $this->client->get('selftest')->json();

		if (isset($response['success']) && $response['success'] === true) {
			return true;
		}

		return false;
	}

	public function authTest()
	{
		$url = 'authTest';

		$response = $this->request($url);

		if (isset($response['success']) && $response['success'] === true) {
			return true;
		}

		return false;
	}

	public function getCompany(array $fields = array())
	{
		$url = 'company';

		return $this->request($url, $fields);
	}

	public function getEvents(array $fields = array())
	{
		$url = 'events';

		return $this->request($url, $fields);
	}

	public function getEvent($event_id, array $fields = array())
	{
		$url = 'events/' . $event_id;

		return $this->request($url, $fields);
	}

	public function getRegistrations($event_id, array $fields = array())
	{
		$url = 'events/' . $event_id . '/registrations';

		return $this->request($url, $fields);
	}

	public function getRegistration($event_id, $registration_id, array $fields = array())
	{
		$url = 'events/' . $event_id . '/registrations/' . $registration_id;

		return $this->request($url, $fields);
	}

	public function createRegistration($event_id, $registration)
	{
		$url = 'events/' . $event_id . '/registrations/';

		return $this->post($url, $registration);
	}

	public function findAddress($eventID, $postcode)
	{
		$url = 'findAddress';
		$fields = [
			'eventID' => [$eventID],
			'postcode' => [$postcode]
		];
		return $this->request($url, $fields);
	}

}
