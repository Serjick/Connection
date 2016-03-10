<?php

namespace Imhonet\Connection\DataFormat\Hash\Http;

use Imhonet\Connection\DataFormat\IMulti;
use Imhonet\Connection\IErrorable;

abstract class Http implements IMulti, IErrorable
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
    private $todo_count = array(
        'curl_multi_select' => null,
        'curl_multi_info_read' => null,
        'curl_multi_exec' => null,
    );

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
            $this->setQueueSize('curl_multi_select', $this->todo_count['curl_multi_select'] - 1);
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
            $this->setQueueSize('curl_multi_exec', $this->getCountRunning());
            --$count;
        }

        $this->setQueueSize('curl_multi_select', $count);
    }

    private function getCountRunning()
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
        $this->todo_count[$queue] = $count > 0 ? $count : 0;

        return $this;
    }

    public function getErrorCode()
    {
        return (int) ($this->getResponseResultCode() !== \CURLE_OK
            || curl_getinfo($this->getResponseHandle(), CURLINFO_HTTP_CODE) >= 400
        );
    }

    /**
     * @inheritDoc
     */
    public function moveNext()
    {
        $this->freeResponse();
        $this->response = null;

        return array_sum($this->todo_count) > 0 || $this->getCountRunning();
    }
}
