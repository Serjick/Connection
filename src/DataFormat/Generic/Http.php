<?php

namespace Imhonet\Connection\DataFormat\Generic;

use Imhonet\Connection\DataFormat\IMulti;
use Imhonet\Connection\IErrorable;
use Imhonet\Connection\Cache\ICachable;

abstract class Http implements IMulti, IErrorable, ICachable
{
    /** @type \Imhonet\Connection\Response\Http */
    private $data;

    private $response;

    /**
     * @param \Imhonet\Connection\Response\Http $data
     * @return self
     */
    public function setData($data)
    {
        $this->data = $data;

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

    /**
     * @return int|null CURLE_*
     */
    private function getResponseResultCode()
    {
        return $this->getResponseData()['result'];
    }

    /**
     * @return array|null
     * @see http://php.net/manual/en/function.curl-multi-info-read.php#refsect1-function.curl-multi-info-read-returnvalues
     */
    private function getResponseData()
    {
        if ($this->response === null) {
            $this->response = $this->data->getResponse();
        }

        return $this->response;
    }

    private function getResponsePrivates()
    {
        return ($handle = $this->getResponseHandle())
            ? json_decode(curl_getinfo($handle, \CURLINFO_PRIVATE), true)
            : null;
    }

    private function freeResponse()
    {
        if ($handle = $this->getResponseHandle()) {
            $this->data->dropHandle($handle);
        }

        $this->response = null;
    }

    public function getErrorCode()
    {
        return (int) ($this->getResponseResultCode() !== \CURLE_OK
            || curl_getinfo($this->getResponseHandle(), \CURLINFO_HTTP_CODE) >= 400
        );
    }

    /**
     * @inheritDoc
     */
    public function moveNext()
    {
        $this->freeResponse();

        return count($this->data) > 0;
    }

    /**
     * @inheritDoc
     */
    public function getCacheKey()
    {
        return md5(get_class($this));
    }
}
