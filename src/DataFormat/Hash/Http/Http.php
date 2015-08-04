<?php

namespace Imhonet\Connection\DataFormat\Hash\Http;

use Imhonet\Connection\DataFormat\IMulti;

abstract class Http implements IMulti
{
    /** @type resource */
    private $handle;

    /**
     * @type array
     * @see http://php.net/manual/en/function.curl-multi-info-read.php#refsect1-function.curl-multi-info-read-returnvalues
     */
    private $response;

    /**
     * amount of unhandled responses
     */
    private $todo_count;

    public function setData($data)
    {
        $this->handle = $data;

        return $this;
    }

    public function getIndex()
    {
        return $this->getResponsePrivates()['id'];
    }

    protected function getResponse()
    {
        $result = null;

        if ($handle = $this->getResponseHandle()) {
            $result = curl_multi_getcontent($handle);
        }

        return $result;
    }

    /**
     * @return resource|null
     */
    private function getResponseHandle()
    {
        return $this->getResponseData()['handle'];
    }

    private function getResponseData()
    {
        if (!$this->response) {
            if (!$this->response = curl_multi_info_read($this->handle)) {
                $this->waitResponse();
                $this->response = curl_multi_info_read($this->handle);
            }

            if ($this->response) {
                --$this->todo_count;
            }
        }

        return $this->response;
    }

    private function getResponsePrivates()
    {
        return ($handle = $this->getResponseHandle())
            ? json_decode(curl_getinfo($handle, \CURLINFO_PRIVATE), true)
            : null;
    }

    private function waitResponse()
    {
        $count = curl_multi_select($this->handle, -1);

        if ($count > 0) {
            curl_multi_exec($this->handle, $still_running);
            $count += $still_running;
        }

        if ($this->todo_count === null) {
            $this->todo_count = $count;
        }
    }

    private function freeResponse()
    {
        if ($this->response) {
            curl_multi_remove_handle($this->handle, $this->getResponseHandle());
        }
    }

    /**
     * @inheritDoc
     */
    public function moveNext()
    {
        $this->freeResponse();
        $this->response = null;

        return $this->todo_count > 0;
    }
}
