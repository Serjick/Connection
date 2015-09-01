<?php

namespace Imhonet\Connection\Query\Elastic;


use Elasticsearch\Client as Elastic;
use GuzzleHttp\Ring\Future\FutureArrayInterface;
use Imhonet\Connection\Query\Query;

/**
 * @todo multi
 */
class Search extends Query
{
    const LIMIT_MAX = 2147483647;

    private $index = array();
    private $filter = array();
    private $limit = self::LIMIT_MAX;
    private $offset = 0;
    private $sort_field;
    private $sort_order = SORT_ASC;

    private $response = array();
    private $is_dispatched = false;

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
     * @return FutureArrayInterface[]
     */
    public function getResponses()
    {
        if (!$this->is_dispatched) {
            foreach ($this->getRequests() as $request) {
                $this->response = $this->getResource()->search($request);
            }

            $this->is_dispatched = true;
        }

        return [$this->response];
    }

    private function getRequests()
    {
        $result = array();

        foreach (array_keys($this->index) as $query_id) {
            $result[] = array(
                'index' => $this->resource->getDatabase(),
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

        return $result;
    }

    private function getFilter()
    {
        $filters = array();

        foreach ($this->filter as $field => $values) {
            $field = current($this->index) . '.' . $field;

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
        $responses = $this->getResponses();

        return current($responses) === false ? null : current($responses)['hits']['total'];
    }

    /**
     * @inheritdoc
     */
    public function getCount()
    {
        $responses = $this->getResponses();

        return current($responses) === false ? null : sizeof(current($responses)['hits']['hits']);
    }

    /**
     * @inheritdoc
     */
    public function getLastId()
    {
    }
}
