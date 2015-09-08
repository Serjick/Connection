<?php

namespace Imhonet\Connection\Query\Http;

use Imhonet\Connection\Query\Query;
use Imhonet\Connection\Query\TImmutable;

abstract class Http extends Query
{
    use TImmutable;

    const PARAMS_GET = 1;
    const PARAMS_POST_ARRAY = 2;
    const PARAMS_POST_JSON = 3;

    private $url;
    private $params = array();
    private $headers = array();

    /**
     * @var resource|null
     */
    private $handle;
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
        $instance->success = null;

        return $instance;
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
        $this->params[$type] = $values;

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

        return $this;
    }

    /**
     * @return resource cURL multi handle
     */
    public function execute()
    {
        return $this->getParent()->runQuery();
    }

    private function runQuery()
    {
        $this->success = $this->success === null ? true : $this->success;
        $multi_handle = $this->getRequestMulti();
        $handles = $this->getResponses(function(self $query) {
            return !$query->hasResponse();
        });

        foreach ($handles as $handle) {
            if (\CURLM_OK === $error = curl_multi_add_handle($multi_handle, $handle)) {
                $need_exec = true;
            } else {
                $this->success = false;
            }
        }

        if (!empty($need_exec)) {
            if (\CURLM_OK < $error = curl_multi_exec($multi_handle, $running)) {
                $this->success = false;
            }
        }

        return $multi_handle;
    }

    /**
     * @return resource cURL handle
     */
    protected function getResponse()
    {
        if (!$this->hasResponse()) {
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
        $handle = curl_copy_handle($this->getResource());
        $url = $this->url;

        if ($params = $this->getParams(self::PARAMS_GET)) {
            $url .= '?' . http_build_query($params);
        }

        if ($params = $this->getParams(self::PARAMS_POST_ARRAY)) {
            $post = http_build_query($params);
        } elseif ($params = $this->getParams(self::PARAMS_POST_JSON)) {
            $post = json_encode($params);
        }

        curl_setopt($handle, \CURLOPT_PRIVATE, json_encode(['id' => $this->query_id]));
        curl_setopt($handle, \CURLOPT_URL, $url);

        if (!empty($post)) {
            curl_setopt($handle, \CURLOPT_POST, true);
            curl_setopt($handle, \CURLOPT_POSTFIELDS, $post);
        }

        curl_setopt($handle, \CURLOPT_HTTPHEADER, $this->getHeaders());

        return $handle;
    }

    private function getRequestMulti()
    {
        return $this->handle ? : $this->handle = curl_multi_init();
    }

    private function getParams($type)
    {
        return isset($this->params[$type]) ? $this->params[$type] : array();
    }

    private function getHeaders()
    {
        $result = $this->headers;

        if ($this->getParams(self::PARAMS_POST_JSON)) {
            $result[] = 'Content-Type: application/json';
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

    public function getDebugInfo($type = self::INFO_TYPE_QUERY)
    {
        switch ($type) {
            case self::INFO_TYPE_QUERY:
                $result = $this->url . '?' . http_build_query(array_merge(
                    $this->getParams(self::PARAMS_GET),
                    $this->getParams(self::PARAMS_POST_ARRAY),
                    $this->getParams(self::PARAMS_POST_JSON)
                ));
                break;
            default:
                $result = parent::getDebugInfo($type);
        }

        return $result;
    }
}
