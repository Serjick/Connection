<?php

namespace Imhonet\Connection\DataFormat\Hash\Http;


class Http
{
    /** @type resource */
    private $handle;
    private $last_response_id;

    public function setData($data)
    {
        $this->handle = $data;

        return $this;
    }

    protected function getResponse()
    {
        $data = $this->getResponseData();
        $handle = $data['handle'];

        $this->last_response_id = curl_getinfo($handle, \CURLINFO_PRIVATE);
        $result = curl_multi_getcontent($handle);
        curl_multi_remove_handle($this->handle, $handle);

        return $result;
    }

    private function getResponseData()
    {
        if (!$result = curl_multi_info_read($this->handle)) {
            $this->waitResponse();
            $result = curl_multi_info_read($this->handle);
        }

        return $result;
    }

    private function waitResponse()
    {
        curl_multi_select($this->handle, -1);
        curl_multi_exec($this->handle, $still_running);
    }
}
