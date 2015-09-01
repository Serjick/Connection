<?php

namespace Imhonet\Connection\Query\Elastic;


use Elasticsearch\Client as Elastic;
use GuzzleHttp\Ring\Future\FutureArrayInterface;
use Imhonet\Connection\Query\Query;

class Get extends Query
{
    private $ids = array();
    private $fields = array();

    /**
     * @param array $ids
     * @return self
     */
    public function addIds($ids)
    {
        $this->ids[] = $ids;

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

        foreach ($this->ids as $ids) {
            $result[] = array(
                'index' => $this->resource->getDatabase(),
                'type' => $this->resource->getIndexName(),
                'realtime' => true,
                '_source' => $this->fields ? : true,
                'body' => array(
                    'ids' => $ids,
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
        // TODO: Implement getErrorCode() method.
    }

    /**
     * @inheritdoc
     */
    public function getCountTotal()
    {
        // TODO: Implement getCountTotal() method.
    }

    /**
     * @inheritdoc
     */
    public function getCount()
    {
        // TODO: Implement getCount() method.
    }

    /**
     * @inheritdoc
     */
    public function getLastId()
    {
    }
}
