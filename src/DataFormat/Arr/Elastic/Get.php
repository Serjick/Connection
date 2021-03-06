<?php

namespace Imhonet\Connection\DataFormat\Arr\Elastic;

use GuzzleHttp\Ring\Future\FutureArrayInterface;
use Elasticsearch\Common\Exceptions\ElasticsearchException;
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

            try {
                foreach ($response['docs'] as $doc) {
                    if ($doc['found']) {
                        $result[] = $doc['_source'];
                    }
                }
            } catch (ElasticsearchException $e) {
            }

            assert(empty($e), isset($e) ? (string) $e : null);
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
