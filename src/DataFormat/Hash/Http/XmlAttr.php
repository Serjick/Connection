<?php

namespace Imhonet\Connection\DataFormat\Hash\Http;

use Imhonet\Connection\DataFormat\Generic\Http;
use Imhonet\Connection\DataFormat\IHash;

/**
 * Class XmlAttr
 * @todo support not only root node
 * @package Imhonet\Connection\DataFormat\Hash\Http
 */
class XmlAttr extends Http implements IHash
{
    private $data;

    private $result;

    /**
     * @inheritdoc
     */
    public function formatData()
    {
        if (!$this->result) {
            $this->result = $this->isValid() ? $this->getAttributes() : array();
        }

        return $this->result;
    }

    private function isValid()
    {
        return $this->getResponse() !== false;
    }

    /**
     * @return \SimpleXMLElement|bool
     */
    protected function getResponse()
    {
        return $this->data ? : $this->data = simplexml_load_string(parent::getResponse());
    }

    private function getAttributes()
    {
        $result = array();

        foreach ($this->getResponse()->attributes() as $name => $value) {
            $result[$name] = (string) $value;
        }

        return $result;
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
        $this->data = null;

        return parent::moveNext();
    }
}
