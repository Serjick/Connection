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
    private $headers = array();
    private $dispatched = array();

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
        $url_id = $this->getLastQueryId();

        if (!isset($this->params[$url_id])) {
            $this->params[$url_id] = array();
        }

        $this->params[$url_id][$type] = $values;

        return $this;
    }

    /**
     * @param string $name
     * @param string $value
     * @return self
     */
    public function addHeader($name, $value)
    {
        $url_id = $this->getLastQueryId();

        if (!isset($this->headers[$url_id])) {
            $this->headers[$url_id] = array();
        }

        $this->headers[$url_id][] = $name . ': ' . $value;

        return $this;
    }

    final protected function getLastQueryId()
    {
        return $this->url ? count($this->url) - 1 : 0;
    }

    /**
     * @return resource
     */
    public function execute()
    {
        return $this->getResponse();
    }

    protected function getResponse()
    {
        if ($this->isDispatched() && $this->handle) {
            $handle = $this->handle;
            $requests = $this->getRequests($this->dispatched);
        } else {
            $handle = curl_multi_init();
            $requests = $this->getRequests();
        }

        if ($requests) {
            foreach ($requests as $id => $request) {
                if (curl_multi_add_handle($handle, $request) === CURLM_OK) {
                    $this->dispatched[$id] = true;
                }
            }

            $result = curl_multi_exec($handle, $running);
            $this->success = $result === CURLM_OK;
            $this->handle = $this->success ? $handle : $this->handle;
        }

        return $this->handle;
    }

    private function getRequests(array $filter_query_ids = array())
    {
        $result = array();

        foreach (array_keys($this->url) as $query_id) {
            if (empty($filter_query_ids[$query_id])) {
                $result[] = $this->getRequest($query_id);
            }
        }

        return $result;
    }

    protected function getRequest($query_id)
    {
        $handle = curl_copy_handle($this->getResource());
        $url = $this->getUrl($query_id);

        if ($params = $this->getParams($query_id, self::PARAMS_GET)) {
            $url .= '?' . http_build_query($params);
        }

        if ($params = $this->getParams($query_id, self::PARAMS_POST_ARRAY)) {
            $post = $params;
        } elseif ($params = $this->getParams($query_id, self::PARAMS_POST_JSON)) {
            $post = json_encode($params);
        }

        curl_setopt($handle, \CURLOPT_PRIVATE, json_encode(['id' => $query_id]));
        curl_setopt($handle, \CURLOPT_URL, $url);

        if (!empty($post)) {
            curl_setopt($handle, \CURLOPT_POST, true);
            curl_setopt($handle, \CURLOPT_POSTFIELDS, http_build_query($post));
        }

        curl_setopt($handle, \CURLOPT_HTTPHEADER, $this->getHeaders($query_id));

        return $handle;
    }

    private function getUrl($query_id)
    {
        return isset($this->url[$query_id]) ? $this->url[$query_id] : null;
    }

    private function getParams($query_id, $type)
    {
        return isset($this->params[$query_id][$type]) ? $this->params[$query_id][$type] : array();
    }

    private function getHeaders($query_id)
    {
        $result = isset($this->headers[$query_id]) ? $this->headers[$query_id] : array();

        if ($this->getParams($query_id, self::PARAMS_POST_JSON)) {
            $result[] = 'Content-Type: application/json';
        }

        return $result;
    }

    protected function isDispatched()
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
                $result = array();

                foreach (array_keys($this->url) as $query_id) {
                    $params = array_merge(
                        $this->getParams($query_id, self::PARAMS_GET),
                        $this->getParams($query_id, self::PARAMS_POST_ARRAY),
                        $this->getParams($query_id, self::PARAMS_POST_JSON)
                    );
                    $result[] = $this->getUrl($query_id) . '?' . http_build_query($params);
                }

                $result = implode(PHP_EOL, $result);
                break;
            default:
                $result = parent::getDebugInfo($type);
        }

        return $result;
    }
}
