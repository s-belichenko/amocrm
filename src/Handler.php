<?php

namespace AmoCRM;

use http\Env\Response;

class Handler
{
    private $domain;
    private $debug;
    private $errors;
    private $pathLog;

    public $user;
    public $key;
    public $config = [];
    public $headers_response;
    public $result;
    public $last_insert_id;

    public function __construct($domain = null, $user = null, $key, $debug = false, $pathLog = '')
    {
        $this->domain = $domain;
        $this->user = $user;
        $this->key = $key;
        $this->debug = $debug;
        $this->pathLog = $pathLog;

        if (empty($key)) {
            throw new \Exception('Api ключ не указан');
        }

        $this->request(new Request(Request::AUTH, $this));
    }

    public function request(Request $request)
    {
        $headers_response = [];
        $headers = ['Content-Type: application/json'];
        if ($date = $request->getIfModifiedSince()) {
            $headers[] = 'IF-MODIFIED-SINCE: ' . $date;
        }
        $headers[] = 'X-Requested-With: XMLHttpRequest';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://' . $this->domain . '.amocrm.ru' . $request->url);
        curl_setopt($ch, CURLOPT_USERAGENT, 'amoCRM-API-client/1.0');
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_COOKIEFILE, __DIR__ . '/../config/cookie_' . $this->key . '.txt');
        curl_setopt($ch, CURLOPT_COOKIEJAR, __DIR__ . '/../config/cookie_' . $this->key . '.txt');

        curl_setopt($ch, CURLOPT_HEADERFUNCTION,
            function($curl, $header) use (&$headers_response)
            {
                $len = strlen($header);
                $header = explode(':', $header, 2);
                if (count($header) < 2) // ignore invalid headers
                    return $len;

                $name = strtolower(trim($header[0]));
                if (!array_key_exists($name, $headers_response))
                    $headers_response[$name] = [trim($header[1])];
                else
                    $headers_response[$name][] = trim($header[1]);

                return $len;
            }
        );

        if ($request->post) {
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($request->params));
        }

        $result = curl_exec($ch);
        $info = curl_getinfo($ch);
        $error = curl_error($ch);

        if (0) {
            if ($request->object == null) {
                $pathLog = $this->pathLog . $request->request_type . '.json';
            } elseif(isset($request->params['limit_offset'])) {
                $pathLog = $this->pathLog . $request->object[0] . '_' . $request->object[1] .'_' . $request->params['limit_offset'] .'.json';
            } else {
                $pathLog = $this->pathLog . $request->object[0] . '_' . $request->object[1].'.json';
            }
            file_put_contents($pathLog, $result);
        }

        curl_close($ch);

        if ($error) {
            throw new \Exception($error);
        }

        $this->headers_response = $headers_response;
        $this->result = json_decode($result, $request->format);

        if (json_last_error() != JSON_ERROR_NONE) {
            return $this;
        }

        if ($request->format == Request::FORMAT_ARRAY) {
            return $this->parseArray($request, $info);
        }

        return $this->parseObject($request, $info);
    }

    public function parseArray($request, $info)
    {
        if (floor($info['http_code'] / 100) >= 3) {
            if (!$this->debug) {
                $message = $this->result['response']['error'];
            } else {
                $error = (isset($this->result['response']['error'])) ? $this->result['response']['error'] : '';
                $error_code = (isset($this->result['response']['error_code'])) ? $this->result['response']['error_code'] : '';
                $description = ($error && $error_code && isset($this->errors[$error_code])) ? $this->errors[$error_code] : '';
                $response = (isset($this->result['response']['error'])) ? $this->result['response']['error'] : '';

                $message = json_encode([
                    'http_code' => $info['http_code'],
                    'response' => $response,
                    'description' => $description
                ], JSON_UNESCAPED_UNICODE);
            }

            throw new \Exception($message);
        }

        $this->result = isset($this->result['response']) ? $this->result['response'] : false;
        $this->last_insert_id = ($request->post && isset($this->result[$request->type][$request->action][0]->id))
            ? $this->result[$request->type][$request->action][0]->id
            : false;

        return $this;
    }

    public function parseObject($request, $info)
    {
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
