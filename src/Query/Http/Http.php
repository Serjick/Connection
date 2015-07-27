<?php

namespace Imhonet\Connection\Query\Http;

use Imhonet\Connection\Query\Query;

abstract class Http extends Query
{
    const PARAMS_GET = 1;
    const PARAMS_POST_ARRAY = 2;
    const PARAMS_POST_JSON = 3;

    private $url = array();
    private $params = array();

    /**
     * @var string|null
     */
    private $response;
    /**
     * @var bool|null
     */
    private $success;

    /**
     * @param string $url
     * @return self
     */
    public function addUrl($url)
    {
        $this->url[] = $url;

        return $this;
    }

    /**
     * @param mixed $values
     * @param int $type
     * @return self
     */
    public function addParams(array $values, $type = self::PARAMS_GET)
    {
        $url_id = $this->getUrlId();

        if (!isset($this->params[$url_id])) {
            $this->params[$url_id] = array();
        }

        $this->params[$url_id][$type] = $values;

        return $this;
    }

    private function getUrlId()
    {
        return $this->url ? count($this->url) - 1 : 0;
    }

    /**
     * @return string|bool
     */
    public function execute()
    {
        return $this->getResponse();
    }

    protected function getResponse()
    {
        if (!$this->hasResponse()) {
            $response = curl_exec($this->getRequest());
            $this->success = $response !== false;
            $this->response = $this->success ? $response : $this->response;
        }

        return $this->response;
    }

    protected function getRequest()
    {
        $handle = $this->getResource();
        $url = $this->getUrl();
        $params = $this->getParams();
        $headers = array();

        foreach ($params as $type => $values) {
            switch ($type) {
                case self::PARAMS_GET:
                    $url .= '?' . http_build_query($values);
                    break;
                case self::PARAMS_POST_JSON:
                    $headers[] = 'Content-Type: application/json';
                    $post = json_encode($values);
                    break;
                case self::PARAMS_POST_ARRAY:
                    $post = $values;
                    break;
            }
        }

        curl_setopt($handle, \CURLOPT_URL, $url);

        if (isset($post)) {
            curl_setopt($handle, \CURLOPT_POST, true);
            curl_setopt($handle, \CURLOPT_POSTFIELDS, $post);
        }

        if ($headers) {
            curl_setopt($handle, \CURLOPT_HTTPHEADER, $headers);
        }

        return $handle;
    }

    protected function getUrl()
    {
        $url_id = key($this->url);

        return $this->url[$url_id];
    }

    protected function getParams()
    {
        $url_id = key($this->url);

        return isset($this->params[$url_id]) ? $this->params[$url_id] : array();
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
    public function getErrorCode()
    {
        return (int) $this->isError();
    }

    public function getDebugInfo($type = self::INFO_TYPE_QUERY)
    {
        switch ($type) {
            case self::INFO_TYPE_QUERY:
                $params = array();

                foreach ($this->getParams() as $values) {
                    $params = array_merge($params, $values);
                }

                $result = $this->getUrl() . '?' . http_build_query($params);
                break;
            default:
                $result = parent::getDebugInfo($type);
        }

        return $result;
    }
}
