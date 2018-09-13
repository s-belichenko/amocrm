<?php

namespace AmoCRM;

class Handler
{
    private $subdomain;
    private $debug;
    private $errors;
    private $configDir;
    private $domain;

    public $user;
    public $key;
    public $config;
    public $result;
    public $last_insert_id;

    /**
     * Handler constructor.
     *
     * @param null         $subdomain
     * @param null         $user
     * @param bool         $debug
     * @param string|array $config
     * @param string       $domain
     *
     * @throws \Exception
     */
    public function __construct($subdomain = null, $user = null, $debug = false, $config = '', $domain = 'ru')
    {
        $this->subdomain = $subdomain;
        $this->user = $user;
        $this->debug = $debug;
        $this->domain = $domain;

        if (is_array($config)) {
            $this->processConfigArray($config);
        } else {
            $this->processConfigDir($config);
        }

        $this->request(new Request(Request::AUTH, $this));
    }

    /**
     * @param string $configDir
     *
     * @throws \Exception
     */
    private function processConfigDir($configDir)
    {
        $default_config_dir = __DIR__ . '/../config/';
        $this->configDir = empty($configDir)
            ? $default_config_dir
            : preg_match("/\/$/", $configDir)
                ? $configDir
                : $configDir . "/";

        $file_key = $this->configDir . $this->subdomain . '@' . $this->user . '.key';
        $file_config = $this->configDir . 'config@' . $this->subdomain . '.php';

        $key = trim(file_get_contents($file_key));
        $config = trim(file_get_contents($file_config));

        if (!is_readable($this->configDir) || !is_writable($this->configDir)) {
            throw new \Exception('Директория "' . $this->configDir . '" должна быть доступна для чтения и записи');
        }

        if (!file_exists($file_config)) {
            throw new \Exception('Отсутсвует файл с конфигурацией');
        }

        if (empty($config)) {
            throw new \Exception('Файл с конфигурацией пуст');
        }
        if (!file_exists($file_key)) {
            throw new \Exception('Отсутсвует файл с ключом');
        }
        if (empty($key)) {
            throw new \Exception('Файл с ключом пуст');
        }

        $this->key = $key;
        $this->config = include $file_config;

        if ($this->debug) {
            $this->errors = @json_decode(trim(file_get_contents($this->configDir . 'errors.json')));
        }
    }

    /**
     * @param array $configArray
     */
    private function processConfigArray(array $configArray)
    {
        $this->key = $configArray['key'];
        $this->config = $configArray['key_value'];

        if ($this->debug) {
            $this->errors = $configArray['errors'];
        }
    }

    /**
     * @param Request $request
     * @param bool    $arrayable
     *
     * @return $this
     * @throws \Exception
     */
    public function request(Request $request, $arrayable = false)
    {
        $headers = ['Content-Type: application/json'];
        if ($date = $request->getIfModifiedSince()) {
            $headers[] = 'IF-MODIFIED-SINCE: ' . $date;
        }

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://$this->subdomain.amocrm.$this->domain/private/api/$request->url");
        curl_setopt($ch, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, $this->configDir . 'cookie.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, $this->configDir . 'cookie.txt');

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
                    'http_code'   => $info['http_code'],
                    'response'    => $response,
                    'description' => $description
                ], JSON_UNESCAPED_UNICODE);
            }

            throw new \Exception($message);
        }

        $this->result = isset($this->result->response) ?
            ($arrayable) ?
                json_decode(json_encode($this->result->response), true)
                :
                $this->result->response
            :
            false;
        $this->last_insert_id = ($request->post && isset($this->result->{$request->type}->{$request->action}[0]->id))
            ? $this->result->{$request->type}->{$request->action}[0]->id
            : false;

        return $this;
    }
}
