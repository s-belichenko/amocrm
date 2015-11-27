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
	public $type;
	public $action;
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
		if (!is_array($this->params)) {
			$this->params = [$this->params];
		}

		$type = $this->params[0]->type;
		$id = $this->params[0]->id;

		$action = (isset($id)) ? 'update' : 'add';
		$params = [];
		$params['request'][$type][$action] = $this->params;

		$this->post = true;
		$this->type = $type;
		$this->action = $action;
		$this->url = 'v2/json/'.$this->type.'/set';
		$this->params = $params;
	}
}
