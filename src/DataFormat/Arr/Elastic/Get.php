<?php

namespace Imhonet\Connection\DataFormat\Arr\Elastic;

use GuzzleHttp\Ring\Future\FutureArrayInterface;
use Imhonet\Connection\DataFormat\IArr;

/**
 * @todo handle multi queries
 */
class Get implements IArr
{
    /**
     * @type FutureArrayInterface[]
     */
    private $data;

    /**
     * @inheritdoc
     * @param FutureArrayInterface[] $data
     */
    public function setData($data)
    {
        $this->data = $data;
        assert($this->isValid());

        return $this;
    }

    private function isValid()
    {
        return is_array($this->data) && array_reduce($this->data, function ($carry, $item) {
            return $carry && $item instanceof FutureArrayInterface;
        }, true);
    }

    private function current()
    {
        return current($this->data);
    }

    /**
     * @inheritdoc
     */
    public function formatData()
    {
        $result = array();

        if ($this->isValid()) {
            $response = $this->current();

            foreach ($response['docs'] as $doc) {
                if ($doc['found']) {
                    $result[] = $doc['_source'];
                }
            }
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
