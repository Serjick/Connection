<?php

namespace Imhonet\Connection\Query\Http;

class Get extends Http
{
    /**
     * @inheritdoc
     */
    public function addParams(array $values, $type = self::PARAMS_GET)
    {
        assert($type === self::PARAMS_GET);

        return parent::addParams($values, self::PARAMS_GET);
    }

    protected function getRequest($query_id)
    {
        $handle = parent::getRequest($query_id);
        curl_setopt($handle, \CURLOPT_HTTPGET, true);

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
