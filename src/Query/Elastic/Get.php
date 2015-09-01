<?php

namespace Imhonet\Connection\Query\Elastic;


use Elasticsearch\Client as Elastic;
use GuzzleHttp\Ring\Future\FutureArrayInterface;
use Imhonet\Connection\Query\Query;

/**
 * @todo multi
 */
class Get extends Query
{
    private $index = array();
    private $ids;
    private $fields;

    /**
     * @todo immutable
     * @param string $index
     * @return self
     */
    public function withIndex($index)
    {
        $this->index[] = $index;

        return $this;
    }

    /**
     * @param array $ids
     * @return self
     */
    public function setIds($ids)
    {
        $this->ids = $ids;

        return $this;
    }

    /**
     * @param string[] $fields
     * @return self
     */
    public function setFields(array $fields)
    {
        $this->fields = $fields;

        return $this;
    }

    /**
     * @return FutureArrayInterface[]
     */
    public function execute()
    {
        $result = array();

        foreach ($this->getRequests() as $request) {
            $result[] = $this->getResource()->mget($request);
        }

        return $result;
    }

    private function getRequests()
    {
        $result = array();

        foreach ($this->index as $index) {
            $result[] = array(
                'index' => $this->resource->getDatabase(),
                'type' => $index,
                'realtime' => true,
                '_source' => $this->fields ? : true,
                'body' => array(
                    'ids' => $this->ids,
                ),
                'client' => array(
                    'future' => 'lazy',
                )
            );
        }

        return $result;
    }

    /**
     * @return Elastic
     */
    protected function getResource()
    {
        return parent::getResource();
    }

    /**
     * @inheritdoc
     */
    public function getErrorCode()
    {
    }

    /**
     * @inheritdoc
     */
    public function getCountTotal()
    {
        return $this->getCount();
    }

    /**
     * @inheritdoc
     */
    public function getCount()
    {
        return sizeof($this->ids);
    }

    /**
     * @inheritdoc
     */
    public function getLastId()
    {
    }
}
