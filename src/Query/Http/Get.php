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

    protected function getRequest()
    {
        $handle = parent::getRequest();
        curl_setopt($handle, \CURLOPT_HTTPGET, true);

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
