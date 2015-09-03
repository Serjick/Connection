<?php

namespace Imhonet\Connection\Query\Elastic;

use Elasticsearch\Client as Elastic;
use GuzzleHttp\Ring\Future\FutureArrayInterface;
use Imhonet\Connection\Query\Query;
use Imhonet\Connection\Query\TImmutable;

class Get extends Query
{
    use TImmutable;

    private $index;
    private $ids;
    private $fields;

    private $response;
    private $is_dispatched = false;

    /**
     * @param string $index
     * @return self
     */
    public function withIndex($index)
    {
        $instance = $this->addChild();
        $instance->index = $index;

        return $instance;
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
        return $this->getResponses();
    }

    /**
     * @return FutureArrayInterface|null
     */
    protected function getResponse()
    {
        if (!$this->is_dispatched) {
            try {
                $this->response = $this->getResource()->mget($this->getRequest());
            } catch (\Exception $e) {
                $this->error = $e;
            }

            $this->is_dispatched = true;
        }

        return $this->response;
    }

    private function getRequest()
    {
        return array(
            'index' => $this->resource->getDatabase(),
            'type' => $this->index,
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

    /**
     * @inheritdoc
     * @return Elastic
     */
    protected function getResource()
    {
        return parent::getResource();
    }

    protected function getErrorCodeCurrent()
    {
        return (int) $this->getResponse() === null || $this->error !== null;
    }

    /**
     * @inheritdoc
     */
    protected function getCountTotalCurrent()
    {
        return $this->getCount();
    }

    /**
     * @inheritdoc
     */
    protected function getCountCurrent()
    {
        return sizeof($this->ids);
    }

    /**
     * @inheritdoc
     */
    protected function getLastIdCurrent()
    {
    }
}
