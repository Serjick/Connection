<?php

namespace Imhonet\Connection\Query\Couchbase;

use Imhonet\Connection\Query\Query;

class Get extends Query
{
    private $ids;

    /**
     * @var array|float
     */
    private $response = NAN;

    public function setIds(array $ids)
    {
        $this->ids = $ids;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        return $this->getResponse();
    }

    private function getResponse()
    {
        if (!$this->hasResponse()) {
            try {
                $this->response = $this->getResource()->getMulti($this->ids);
            } catch (\Exception $e) {
                $this->response = null;
            }
        }

        return $this->response;
    }

    /**
     * @inheritdoc
     * @return \Couchbase
     */
    protected function getResource()
    {
        return parent::getResource();
    }

    private function hasResponse()
    {
        return !is_float($this->response) || !is_nan($this->response);
    }

    private function isError()
    {
        return $this->getResponse() === null;
    }

    /**
     * @inheritdoc
     */
    public function getErrorCode()
    {
        return (int) $this->isError();
    }

    /**
     * @inheritdoc
     */
    public function getCountTotal()
    {
        return null;
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
}
