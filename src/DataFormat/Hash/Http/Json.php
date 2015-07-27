<?php

namespace Imhonet\Connection\DataFormat\Hash\Http;

use Imhonet\Connection\DataFormat\IHash;

class Json implements IHash
{
    /**
     * @var string|bool
     */
    private $data;
    private $data_decoded;

    /**
     * @inheritdoc
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function formatData()
    {
        return $this->isValid() ? (array) $this->getDecoded() : array();
    }

    private function isValid()
    {
        return is_string($this->data) && $this->isAssoc();
    }

    private function isAssoc()
    {
        return is_object($this->getDecoded());
    }

    private function getDecoded()
    {
        return $this->data_decoded ? : $this->data_decoded = json_decode($this->data);
    }

    /**
     * @inheritdoc
     */
    public function formatValue()
    {
    }
}
