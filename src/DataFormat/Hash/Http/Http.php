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
    private $todo_count = array();

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

    /**
     * @return int|null CURLE_*
     */
    private function getResponseResultCode()
    {
        return $this->getResponseData()['result'];
    }

    private function getResponseData()
    {
        if ($this->response === null) {
            while (false === $this->response = curl_multi_info_read($this->handle, $msgs_in_queue)) {
                $this->waitTransportActivity();
            }

            $this->setQueueSize('curl_multi_info_read', $msgs_in_queue);
        }

        return $this->response;
    }

    private function getResponsePrivates()
    {
        return ($handle = $this->getResponseHandle())
            ? json_decode(curl_getinfo($handle, \CURLINFO_PRIVATE), true)
            : null;
    }

    private function waitTransportActivity()
    {
        $count = curl_multi_select($this->handle);

        if ($count > 0) {
            $this->setQueueSize('curl_multi_exec', $this->getRunningCount());
            --$count;
        }

        $this->setQueueSize('curl_multi_select', $count == -1 ? 0 : $count);
    }

    private function getRunningCount()
    {
        curl_multi_exec($this->handle, $still_running);

        return $still_running;
    }

    private function freeResponse()
    {
        if ($handle = $this->getResponseHandle()) {
            curl_multi_remove_handle($this->handle, $handle);
        }
    }

    /**
     * @param string $queue
     * @param int $count
     * @return self
     */
    private function setQueueSize($queue, $count)
    {
        $this->todo_count[$queue] = $count;

        return $this;
    }

    /**
     * @todo make it public
     * @return int
     */
    private function getErrorCode()
    {
        return (int) ($this->getResponseResultCode() !== \CURLE_OK);
    }

    /**
     * @inheritDoc
     */
    public function moveNext()
    {
        $this->freeResponse();
        $this->response = null;

        return array_sum($this->todo_count) > 0 || $this->getRunningCount();
    }
}
