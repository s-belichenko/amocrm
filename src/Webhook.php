<?php

namespace AmoCRM;

class Webhook
{
	private $hooks;
	private $config;

	public function __construct()
	{
		$this->hooks = [];
		$this->config = [];
	}

	private function getCallbacks($name)
	{
		return isset($this->hooks[$name]) ? $this->hooks[$name] : [];
	}

	public function on($name, $callback)
	{
		if (!is_callable($callback, true)) {
			throw new \InvalidArgumentException(sprintf('Invalid callback: %s.', print_r($callback, true)));
		}

		if (!is_array($name)) {
			$name = [$name];
		}

		foreach ($name as $event) {
			$this->hooks[$event][] = $callback;
		}
	}

	public function listen()
	{
		if (!$listen = isset($_POST['account'])) {
			return false;
		}

		$post = $_POST;
		$domain = $post['account']['subdomain'];
		$file_config = __DIR__.'/../config/config@'.$domain.'.php';

		if (file_exists($file_config) && !empty(trim(file_get_contents($file_config)))) {
			$this->config = include $file_config;
		}

		unset($post['account']);

		$event = 0;
		$entity = 0;
		$data = [];
		foreach ($post as $key => $value) {
			foreach ($value as $jkey => $jvalue) {
				$entity = $jvalue[0];
				$event  = (isset($entity['type']) && $entity['type'] == 'company') ? 'companies' : $key;
				$event .= '-'.$jkey;
			}
		}

		foreach($this->getCallbacks($event) as $callback) {
			call_user_func($callback, $domain, $entity['id'], $entity, $this->config);
		}
	}
}
