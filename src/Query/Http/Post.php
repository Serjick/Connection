<?php

namespace Imhonet\Connection\Query\Http;

class Post extends Http
{
    protected function getRequest()
    {
        $handle = parent::getRequest();
        curl_setopt($handle, \CURLOPT_CUSTOMREQUEST, 'POST');

        return $handle;
    }

    public function getCountTotalCurrent()
    {
    }

    public function getCountCurrent()
    {
    }

    public function getLastIdCurrent()
    {
    }
}
