<?php

namespace Imhonet\Connection\Query\Elastic;

use Elasticsearch\Client as Elastic;
use GuzzleHttp\Ring\Future\FutureArrayInterface;
use Imhonet\Connection\Query\Query;
use Imhonet\Connection\Query\TImmutable;

/**
 * @todo multi
 */
class Search extends Query
{
    use TImmutable;

    const LIMIT_MAX = 2147483647;

    private $index;
    private $filter = array();
    private $limit = self::LIMIT_MAX;
    private $offset = 0;
    private $sort_field;
    private $sort_order = SORT_ASC;

    private $response = array();
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
     * @param string $field
     * @param array $values
     * @return self
     */
    public function addFilter($field, $values)
    {
        $this->filter[$field] = $values;

        return $this;
    }

    /**
     * @param int $offset
     * @param int $limit
     * @return self
     */
    public function setLimit($offset, $limit = self::LIMIT_MAX)
    {
        $this->offset = $offset;
        $this->limit = $limit;

        return $this;
    }

    /**
     * @param string $field
     * @param int $order SORT_*
     * @return $this
     */
    public function setSort($field, $order = SORT_ASC)
    {
        $this->sort_field = $field;
        $this->sort_order = $order;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        return $this->getResponses();
    }

    /**
     * @return FutureArrayInterface
     */
    public function getResponse()
    {
        if (!$this->is_dispatched) {
            try {
                $this->response = $this->getResource()->search($this->getRequest());
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
            '_source' => false,
            'body' => array(
                'query' => array(
                    'filtered' => array(
                        'filter' => $this->getFilter(),
                    ),
                ),
                "from" => $this->offset,
                "size" => $this->limit,
                "sort" => $this->sort_field
                    ? [array($this->sort_field => $this->sort_order === SORT_DESC ? 'desc' : 'asc')]
                    : [],
            ),
            'client' => array(
                'future' => 'lazy',
            )
        );
    }

    private function getFilter()
    {
        $filters = array();

        foreach ($this->filter as $field => $values) {
            foreach ($values as $value) {
                $filters[] = array(
                    'term' => array($field => $value)
                );
            }
        }

        return array(
            'bool' => array('should' => $filters),
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

    /**
     * @inheritdoc
     */
    protected function getErrorCodeCurrent()
    {
        return (int) $this->getResponse() === null || $this->error !== null;
    }

    /**
     * @inheritdoc
     */
    protected function getCountTotalCurrent()
    {
        $response = $this->getResponse();

        return $response ? $response['hits']['total'] : null;
    }

    /**
     * @inheritdoc
     */
    protected function getCountCurrent()
    {
        $response = $this->getResponse();

        return $response ? sizeof($response['hits']['hits']) : null;
    }

    /**
     * @inheritdoc
     */
    protected function getLastIdCurrent()
    {
    }

    protected function getDebugInfoCurrent($type = self::INFO_TYPE_QUERY)
    {
        switch ($type) {
            case self::INFO_TYPE_BLOCKING:
                $result = self::BLOCKING_FREE;
                break;
            default:
                $result = parent::getDebugInfo($type);
        }

        return $result;
    }
}
