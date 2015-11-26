<?php

namespace AmoCRM;

class Request
{
	const AUTH = 1;
	const INFO = 2;
	const GET = 3;
	const SET = 4;

	public $post;
	public $url;
	public $params;

	private $object;

	public function __construct($request_type = null, $params = null, $object = null)
	{
		$this->post = false;
		$this->params = $params;
		$this->object = $object;

		switch ($request_type) {
			case Request::AUTH:
				$this->createAuthRequest();
				break;
			case Request::INFO:
				$this->createInfoRequest();
				break;
			case Request::GET:
				$this->createGetRequest();
				break;
			case Request::SET:
				$this->createPostRequest();
				break;
		}
	}

	private function createAuthRequest()
	{
		$this->post = true;
		$this->url = 'auth.php?type=json';

		$this->params = [
			'USER_LOGIN' => $this->params->user,
			'USER_HASH' => $this->params->key
		];
	}

	private function createInfoRequest()
	{
		$this->url = 'v2/json/accounts/current';
	}

	private function createGetRequest()
	{
		$this->url = 'v2/json/'.$this->object[0].'/'.$this->object[1];

		if (count($this->params)) {
			$i = 1;
			foreach ($this->params as $key => $value) {
				$this->url .= ($i == 1) ? '?' : '&';
				$this->url .= $key.'='.$value;
				$i++;
			}
		}
	}

	private function createPostRequest()
	{
		$this->post = true;
		$this->url = 'v2/json/'.$this->params->_name.'/set';

		$update = isset($this->params->id);
		$object = array_filter((array)$this->params);
		$action = ($update) ? 'update' : 'add';

		$params = [];
		$params['request'][$this->params->_name][$action] = [(array)$object];
		unset($params['request'][$this->params->_name][$action]['_name']);

		$this->params = $params;
	}
}
