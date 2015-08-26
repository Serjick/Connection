<?php

namespace Imhonet\Connection\DataFormat\Hash\Http;

use Imhonet\Connection\DataFormat\IHash;

class Json extends Http implements IHash
{
    private $response;
    private $data;

    /**
     * @inheritdoc
     */
    public function formatData()
    {
        if ($this->data === null) {
            $this->data = $this->isValid() ? json_decode(parent::getResponse(), true) : array();
        }

        return $this->data;
    }

    private function isValid()
    {
        return substr($this->getResponse(), 0, 1) == '{';
    }

    protected function getResponse()
    {
        return $this->response ? : $this->response = parent::getResponse();
    }

    /**
     * @inheritdoc
     */
    public function formatValue()
    {
    }

    /**
     * @inheritDoc
     */
    public function moveNext()
    {
        $this->data = $this->response = null;

        return parent::moveNext();
    }
}
