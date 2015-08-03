<?php

namespace Imhonet\Connection\DataFormat\Hash\Http;

use Imhonet\Connection\DataFormat\IHash;

class Json extends Http implements IHash
{
    private $data;

    /**
     * @inheritdoc
     */
    public function formatData()
    {
        return $this->isValid() ? (array) $this->getResponse() : array();
    }

    private function isValid()
    {
        return $this->isAssoc();
    }

    private function isAssoc()
    {
        return is_object($this->getResponse());
    }

    protected function getResponse()
    {
        return $this->data ? : $this->data = json_decode(parent::getResponse());
    }

    /**
     * @inheritdoc
     */
    public function formatValue()
    {
    }
}
