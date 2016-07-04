<?php

namespace Imhonet\Connection\Response;

class Http implements \Countable
{
    /**
     * @type resource|bool
     */
    private $handle = false;
    private $handles = array();

    /**
     * @param resource $handle curl handle
     * @return self
     */
    public function addHandle($handle)
    {
        $id = (int) $handle;
        $this->handles[$id] = $handle;

        return $this;
    }

    /**
     * @param resource $handle curl handle
     * @return self
     */
    public function dropHandle($handle)
    {
        $id = (int) $handle;
        assert(isset($this->handles[$id]));

        if ($this->isValid() && curl_multi_remove_handle($this->handle, $handle) === \CURLM_OK) {
            unset($this->handles[$id]);
        }

        return $this;
    }

    /**
     * @param resource|false $handle cURL multi handle
     * @return self
     */
    public function setMultiHandle($handle)
    {
        $this->handle = $handle;

        return $this;
    }

    /**
     * @return array|null
     * @see http://php.net/manual/en/function.curl-multi-info-read.php#refsect1-function.curl-multi-info-read-returnvalues
     */
    public function getResponse()
    {
        $response = null;

        if ($this->isValid()) {
            while (false === $response = curl_multi_info_read($this->handle, $msgs_in_queue)) {
                /*$active = */curl_multi_select($this->handle);
                curl_multi_exec($this->handle, $still_running);
            }
        }

        return $response;
    }

    private function isValid()
    {
        return $this->handle !== false;
    }

    /**
     * @inheritdoc
     */
    public function count()
    {
        return $this->isValid() ? count($this->handles) : 0;
    }
}
