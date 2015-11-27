<?php

namespace AmoCRM;

class Handler
{
	private $domain;
	private $debug;
	private $errors;

	public $user;
	public $key;
	public $config;
	public $result;
	public $last_insert_id;

	public function __construct($domain = null, $user = null, $debug = false)
	{
		$this->domain = $domain;
		$this->user = $user;
		$this->debug = $debug;

		$config_dir = __DIR__.'/../config/';

		$file_key = $config_dir.$this->domain.'@'.$this->user.'.key';
		$file_config = $config_dir.'config@'.$this->domain.'.php';

		if (!file_exists($file_key)) {
			throw new \Exception('Отсутсвует файл с ключом');
		}

		if (!file_exists($file_config)) {
			throw new \Exception('Отсутсвует файл с конфигурацией');
		}

		$key = trim(file_get_contents($file_key));
		$config = trim(file_get_contents($file_config));

		if (empty($key)) {
			throw new \Exception('Файл с ключом пуст');
		}

		if (empty($config)) {
			throw new \Exception('Файл с конфигурацией пуст');
		}

		if ($this->debug) {
			$this->errors = @json_decode(trim(file_get_contents($config_dir.'errors.json')));
		}

		$this->key = $key;
		$this->config = include $file_config;

		$this->request(new Request(Request::AUTH, $this));
	}

	public function request(Request $request)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'https://'.$this->domain.'.amocrm.ru/private/api/'.$request->url);
		curl_setopt($ch, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_COOKIEFILE,  __DIR__.'/../config/cookie.txt');
		curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__.'/../config/cookie.txt');

		if ($request->post) {
			curl_setopt($ch, CURLOPT_POST, true);
			curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request->params));
		}

		$result = curl_exec($ch);
		$info = curl_getinfo($ch);
		$error = curl_error($ch);

		curl_close($ch);

		if ($error) {
			throw new \Exception($error);
		}

		$this->result = json_decode($result);

		if (floor($info['http_code'] / 100) >= 3) {
			if (!$this->debug) {
				$message = $this->result->response->error;
			} else {
				$error = (isset($this->result->response->error)) ? $this->result->response->error : '';
				$error_code = (isset($this->result->response->error_code)) ? $this->result->response->error_code : '';
				$description = ($error && $error_code && isset($this->errors->{$error_code})) ? $this->errors->{$error_code} : '';
				$response = (isset($this->result->response->error)) ? $this->result->response->error : '';

				$message = json_encode([
					'http_code' => $info['http_code'],
					'response' => $response,
					'description' => $description
				], JSON_UNESCAPED_UNICODE);
			}

			throw new \Exception($message);
		}

		$this->result = isset($this->result->response) ? $this->result->response : false;
		$this->last_insert_id = ($request->post && isset($this->result->{$request->type}->{$request->action}[0]->id))
			? $this->result->{$request->type}->{$request->action}[0]->id
			: false;

		return $this;
	}
}
