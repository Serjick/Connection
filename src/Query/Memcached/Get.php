<?php

namespace Imhonet\Connection\Query\Memcached;

use Imhonet\Connection\Query\ICacheGetQuery;
use Imhonet\Connection\Query\Query;

class Get extends Query implements ICacheGetQuery
{
    private $keys;

    /**
     * @var array
     */
    private $response;

    public function setKeys(array $keys)
    {
        $this->keys = $keys;
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
        if ($this->response === null) {
            try {
                $this->response = $this->getResource()->getMulti($this->keys);
            } catch (\Exception $e) {
                $this->response = false;
            }
        }

        return $this->response;
    }

    /**
     * @inheritdoc
     * @return \Memcached
     */
    protected function getResource()
    {
        return parent::getResource();
    }

    private function isError()
    {
        $result = false;

        if ($this->getResponse() === false) {
            try {
                $result = (bool) $this->getResource()->getResultCode();
            } catch (\Exception $e) {
                $result = true;
            }
        }

        return $result;
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
}
