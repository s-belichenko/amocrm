<?php

namespace AmoCRM;

class Webhook
{
    private $hooks;
    private $config;

    /**
     * Webhook constructor.
     */
    public function __construct()
    {
        $this->hooks = [];
        $this->config = [];
    }

    /**
     * @param string $name
     *
     * @return array|mixed
     */
    private function getCallbacks($name)
    {
        return isset($this->hooks[$name]) ? $this->hooks[$name] : [];
    }

    /**
     * @param string|array $name
     * @param callable     $callback
     */
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

    /**
     * @return bool
     */
    public function listen()
    {
        if (!$listen = isset($_POST['account'])) {
            return false;
        }

        $post = $_POST;
        $domain = $post['account']['subdomain'];

        unset($post['account']);

        $event = 0;
        $entity = 0;

        foreach ($post as $key => $value) {
            foreach ($value as $jkey => $jvalue) {
                $entity = $jvalue[0];
                $event = (isset($entity['type']) && $entity['type'] == 'company') ? 'companies' : $key;
                $event .= '-' . $jkey;
            }
        }

        foreach ($this->getCallbacks($event) as $callback) {
            call_user_func($callback, $domain, $entity['id'], $entity, $this->config);
        }

        return true;
    }
}
