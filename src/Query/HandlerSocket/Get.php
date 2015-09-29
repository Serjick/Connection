<?php

namespace Imhonet\Connection\Query\HandlerSocket;

use Imhonet\Connection\Query\Query;

class Get extends Query
{
    private $table;
    private $index;
    private $ids = array();
    private $fields;

    private $response;
    private $index_id;
    private $is_dispatched = false;

    /**
     * @param string $table
     * @return self
     */
    public function setTable($table)
    {
        $this->table = $table;

        return $this;
    }

    /**
     * @param array $ids
     * @param string|null $index PRIMARY if not specified
     * @return self
     */
    public function setIds(array $ids, $index = null)
    {
        $this->index = $index;
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
     * @return array|null
     */
    public function execute()
    {
        return $this->getResponse();
    }

    /**
     * @return array|null
     */
    private function getResponse()
    {
        if (!$this->is_dispatched) {
            try {
                $this->getResource()->select($this->getIndex(), '=', [0], 0, 0, $this->ids);
                $this->response = $this->getResource()->readResponse();
            } catch (\Exception $e) {
                $this->error = $e;
            }

            $this->is_dispatched = true;
        }

        return $this->response;
    }

    /**
     * @return int
     * @throws \HSPHP\ErrorMessage
     */
    private function getIndex()
    {
        return $this->index_id ? : $this->index_id = $this->getResource()->getIndexId(
            $this->resource->getDatabase(),
            $this->table,
            $this->index,
            implode(',', $this->fields));
    }

    /**
     * @inheritdoc
     * @return \HSPHP\ReadSocket
     */
    protected function getResource()
    {
        return parent::getResource();
    }

    public function getErrorCode()
    {
        return (int) $this->getResponse() === null || $this->error !== null;
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
        return sizeof($this->getResponse());
    }

    /**
     * @inheritdoc
     */
    public function getLastId()
    {
    }

    /**
     * @inheritdoc
     */
    public function getDebugInfo($type = self::INFO_TYPE_QUERY)
    {
        switch ($type) {
            case self::INFO_TYPE_QUERY:
                $result = $this->resource->getDatabase()
                    . '::' . $this->table
                    . '::' . $this->index
                    . '::' . implode(',', $this->ids);
                break;
            default:
                $result = parent::getDebugInfo($type);
        }

        return $result;
    }
}
