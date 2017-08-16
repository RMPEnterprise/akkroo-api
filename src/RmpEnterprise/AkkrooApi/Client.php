<?php

namespace RmpEnterprise\AkkrooApi;

class Client
{
	protected $client;
	protected $access_token;

	public function __construct($username, $client_credentials)
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

		$this->auth($username, $client_credentials);
	}

	protected function auth($username, $client_credentials)
	{
		$request = $this->client->createRequest('POST', 'auth');
		$request->setHeader('Authorization', 'Basic ' . $client_credentials);

		$request->setBody(
			\GuzzleHttp\Stream\Stream::factory(
				json_encode(
					array(
						'grant_type' => 'client_credentials',
						'username'   => $username,
						'scope'      => 'PublicAPI'
					)
				)
			)
		);

		$response = $this->client->send($request);
		$data     = $response->json();

		if (isset($data['access_token'])) {
			$this->access_token = $data['access_token'];

			return true;
		}

		return false;
	}

	protected function request($url, array $fields = array())
	{
		if (is_null($this->access_token)) {
			return null;
		}

		$request = $this->client->createRequest('GET', $url);
		$request->setHeader('Authorization', 'Bearer ' . $this->access_token);

		if ($fields) {
			$query = $request->getQuery();
			$query->set('fields', implode(',', $fields));
		}

		$response = $this->client->send($request);

		return $response->json();
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
}