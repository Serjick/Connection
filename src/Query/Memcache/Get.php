<?php

namespace Imhonet\Connection\Query\Memcache;

use Imhonet\Connection\Query\Query;

class Get extends Query
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
                $this->response = $this->getResource()->get($this->keys);
            } catch (\Exception $e) {
                $this->response = false;
            }
        }

        return $this->response;
    }

    /**
     * @inheritdoc
     * @return \Memcache
     */
    protected function getResource()
    {
        return parent::getResource();
    }

    private function isError()
    {
        return $this->getResponse() === false;
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
        return $this->isError() ? 0 : sizeof($this->getResponse());
    }

    /**
     * @inheritdoc
     */
    public function getLastId()
    {
    }
}
