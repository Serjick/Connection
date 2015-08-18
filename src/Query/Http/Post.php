<?php

namespace Imhonet\Connection\Query\Http;

class Post extends Http
{
    protected function getRequest($query_id)
    {
        $handle = parent::getRequest($query_id);
        curl_setopt($handle, \CURLOPT_CUSTOMREQUEST, 'POST');

        return $handle;
    }

    public function getCountTotal()
    {
    }

    public function getCount()
    {
    }

    public function getLastId()
    {
    }
}
