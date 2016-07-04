<?php

namespace Imhonet\Connection\DataFormat\Hash\Http;

use Imhonet\Connection\DataFormat\Generic\Http;
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
            $valid = $this->isValid();
            assert($valid, $this->getResponse());

            if ($valid) {
                $this->data = json_decode($this->getResponse(), true);
                assert(json_last_error() === \JSON_ERROR_NONE, 'json_last_error=' . json_last_error());
            } else {
                $this->data = array();
            }
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

    public function getErrorCode()
    {
        return parent::getErrorCode() || !$this->isValid();
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
