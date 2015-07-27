<?php

namespace Imhonet\Connection\DataFormat\Hash\Http;

use Imhonet\Connection\DataFormat\IHash;

/**
 * Class XmlAttr
 * @todo support not only root node
 * @package Imhonet\Connection\DataFormat\Hash\Http
 */
class XmlAttr implements IHash
{
    /**
     * @var string|bool
     */
    private $data;
    private $data_decoded;

    private $result;

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
        if (!$this->result) {
            $this->result = $this->isValid() ? $this->getAttributes() : array();
        }

        return $this->result;
    }

    private function isValid()
    {
        return is_string($this->data) && $this->getDecoded() !== false;
    }

    /**
     * @return \SimpleXMLElement|bool
     */
    private function getDecoded()
    {
        return $this->data_decoded ? : $this->data_decoded = simplexml_load_string($this->data);
    }

    private function getAttributes()
    {
        $result = array();

        foreach ($this->getDecoded()->attributes() as $name => $value) {
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
}
