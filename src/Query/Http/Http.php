<?php

namespace Imhonet\Connection\Query\Http;

use Imhonet\Connection\Query\Query;
use Imhonet\Connection\Query\TImmutable;
use Imhonet\Connection\Response\Http as ResponseWrapper;

abstract class Http extends Query
{
    use TImmutable;

    const PARAMS_GET = 1;
    const PARAMS_BODY = 2;
    const PARAMS_JSON = 4;

    /** @deprecated */
    const PARAMS_POST_ARRAY = self::PARAMS_BODY;
    /** @deprecated */
    const PARAMS_POST_JSON = 6; // self::PARAMS_BODY | self::PARAMS_JSON

    private $url;
    private $ip;
    private $resolve_ip;
    private $proxy_ip;
    private $params = array();
    private $params_mode = array();
    private $headers = array();
    private $cookies = array();

    /**
     * @var resource|null
     */
    private $handle;
    /**
     * @var ResponseWrapper
     */
    private $wrapper;
    /**
     * @var bool|null
     */
    private $success;

    /**
     * @param string $url
     * @return self
     */
    public function withUrl($url)
    {
        $instance = $this->addChild();
        $instance->url = $url;

        return $instance;
    }

    public function __clone()
    {
        parent::__clone();

        $this->success = null;
    }

    /**
     * @param string $proxy_ip
     * @return self
     */
    public function setProxyIp($proxy_ip)
    {
        $this->proxy_ip = $proxy_ip;

        return $this;
    }

    /**
     * @param string $ip
     * @return self
     */
    public function setIp($ip)
    {
        $this->ip = $ip;

        return $this;
    }

    /**
     * @deprecated
     * @see self::withUrl
     * @param string $url
     * @return self
     */
    public function addUrl($url)
    {
        return $this->withUrl($url);
    }

    /**
     * @param mixed $values
     * @param int $type
     * @return self
     */
    public function addParams(array $values, $type = self::PARAMS_GET)
    {
        $type = $type & self::PARAMS_BODY ? self::PARAMS_BODY : self::PARAMS_GET;
        $this->params[$type] = $values;
        $this->params_mode[$type] = $this->isParamsJson() || $type & self::PARAMS_JSON ? self::PARAMS_JSON : null;

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return self
     */
    public function addHeader($name, $value)
    {
        $this->headers[] = $name . ': ' . $value;

        if (strtolower($name) == 'content-type') {
            $this->params_mode[self::PARAMS_BODY] = strpos($value, 'application/json') === 0 ? self::PARAMS_JSON : null;
        }

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return self
     */
    public function addCookie($name, $value)
    {
        $this->cookies[$name] = $name . '=' . $value;

        return $this;
    }

    /**
     * @param string $resolve_ip
     * @return self
     */
    public function setDNSResolve($resolve_ip)
    {
        $this->resolve_ip = $resolve_ip;

        return $this;
    }

    /**
     * @return ResponseWrapper
     */
    public function execute()
    {
        return $this->getParent()->runQuery();
    }

    /**
     * @return ResponseWrapper
     */
    private function runQuery()
    {
        $result = $this->getResponseWrapper();
        $this->success = $this->success === null ? true : $this->success;
        $multi_handle = $this->getRequestMulti();
        $handles = $this->getResponses(function (self $query) {
            return !$query->hasResponse();
        });

        foreach ($handles as $handle) {
            if (\CURLM_OK === $error = curl_multi_add_handle($multi_handle, $handle)) {
                $need_exec = true;
                $result->addHandle($handle);
            } else {
                $this->success = false;
            }
        }

        if (!empty($need_exec)) {
            if (\CURLM_OK < $error = curl_multi_exec($multi_handle, $running)) {
                $this->success = false;
            }
        }

        return $result->setMultiHandle($multi_handle);
    }

    private function getResponseWrapper()
    {
        return $this->wrapper ? : $this->wrapper = new ResponseWrapper();
    }

    /**
     * @return resource cURL handle
     */
    protected function getResponse()
    {
        if (!$this->hasResponse()) {
            assert($this->disable(null) === false, 'Response of disabled query shouldn\'t be get');

            try {
                $this->handle = $this->getRequest();
            } catch (\Exception $e) {
                $this->error = $e;
            }

            $this->success = (bool) $this->handle;
        }

        return $this->handle;
    }

    protected function getRequest()
    {
        $handle = $this->getResource();
        $url = $this->url;

        if ($params = $this->getParams(self::PARAMS_GET)) {
            $url .= '?' . http_build_query($params);
        }

        if ($params = $this->getParams(self::PARAMS_BODY)) {
            $post = $this->isParamsJson() ? json_encode($params) : http_build_query($params);
        }

        curl_setopt($handle, \CURLOPT_PRIVATE, json_encode(['id' => $this->query_id]));
        curl_setopt($handle, \CURLOPT_URL, $url);

        if ($this->resolve_ip) {
            curl_setopt($handle, \CURLOPT_RESOLVE, [$this->getHostWithPort() . ':' . $this->resolve_ip]);
        }

        if (!empty($post)) {
            curl_setopt($handle, \CURLOPT_POST, true);
            curl_setopt($handle, \CURLOPT_POSTFIELDS, $post);
        }

        if ($this->proxy_ip) {
            curl_setopt($handle, \CURLOPT_PROXY, $this->proxy_ip);
        }

        if ($this->ip) {
            curl_setopt($handle, \CURLOPT_INTERFACE, $this->ip);
        }

        curl_setopt($handle, \CURLOPT_HTTPHEADER, $this->getHeaders());

        return $handle;
    }

    private function getHostWithPort()
    {
        $url = parse_url($this->url);

        return $url['host'] . ':' . (empty($url['port']) ? 80 : $url['port']);
    }

    private function getRequestMulti()
    {
        return $this->handle ? : $this->handle = curl_multi_init();
    }

    private function getParams($type)
    {
        return isset($this->params[$type]) ? $this->params[$type] : array();
    }

    private function isParamsJson()
    {
        return isset($this->params_mode[self::PARAMS_BODY]) && $this->params_mode[self::PARAMS_BODY] == self::PARAMS_JSON;
    }

    private function getHeaders()
    {
        $result = $this->headers;

        if ($this->isParamsJson()) {
            $result[] = 'Content-Type: application/json';
        }

        if ($this->cookies) {
            $result[] = 'Cookie: ' . implode('; ', $this->cookies);
        }

        return $result;
    }

    protected function hasResponse()
    {
        return $this->success !== null;
    }

    /**
     * @return bool
     */
    private function isError()
    {
        return $this->getResponse() === null || $this->success === false;
    }

    /**
     * @inheritdoc
     */
    protected function getErrorCodeCurrent()
    {
        return (int) $this->isError();
    }

    /**
     * @inheritDoc
     */
    public function getCacheKey()
    {
        return md5($this->url . json_encode($this->params) . json_encode($this->getHeaders()));
    }

    protected function getDebugInfoCurrent($type = self::INFO_TYPE_QUERY)
    {
        switch ($type) {
            case self::INFO_TYPE_QUERY:
                $result = $this->url . '?' . urldecode(http_build_query(array_merge(
                    $this->getParams(self::PARAMS_GET),
                    $this->getParams(self::PARAMS_BODY)
                )));
                break;
            case self::INFO_TYPE_ERROR:
                if ($this->hasResponse()) {
                    $http_code = curl_getinfo($this->getResponse(), CURLINFO_HTTP_CODE);
                    $result = $http_code >= 400 ? $http_code : curl_error($this->getResponse());
                }

                if (empty($result)) {
                    $result = parent::getDebugInfo(self::INFO_TYPE_ERROR);
                }
                break;
            case self::INFO_TYPE_BLOCKING:
                $result = self::BLOCKING_FREE;
                break;
            case self::INFO_TYPE_DURATION:
                if ($this->hasResponse()) {
                    $info = curl_getinfo($this->getResponse());
                    $result = $info['http_code'] ? $info['total_time'] : null;
                }
                break;
            default:
                $result = parent::getDebugInfo($type);
        }

        return isset($result) ? (string) $result : '';
    }
}
