<?php

namespace Imhonet\Connection\Query\Memcached;

use Imhonet\Connection\Query\ICacheSetQuery;
use Imhonet\Connection\Query\Query;
use Respect\Validation\Validator;

class Set extends Query implements ICacheSetQuery
{
    private $data = array();
    private $expire = 0;

    /**
     * @var bool
     */
    private $response;

    /**
     * @param array $data [key => value, ...]
     */
    public function setData(array $data)
    {
        $this->data = $data;
    }

    /**
     * @param int $expire
     */
    public function setExpire($value)
    {
        assert(Validator::create()->digit()->noWhitespace()->validate($value));

        $this->expire = $value;
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
                $this->response = $this->getResource()->setMulti($this->data, $this->expire);
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
        return $this->isError() ? 0 : sizeof($this->data);
    }

    /**
     * @inheritdoc
     */
    public function getLastId()
    {
    }
}
