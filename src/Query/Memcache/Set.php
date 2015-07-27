<?php

namespace Imhonet\Connection\Query\Memcache;

use Imhonet\Connection\Query\Query;
use Respect\Validation\Validator;

class Set extends Query
{
    private $data = array();
    private $expire = 0;
    private $count = 0;

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
     * @param int $value
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
            $this->response = true;

            try {
                $resource = $this->getResource();
            } catch (\Exception $e) {
                $this->response = false;
            }

            if (isset($resource)) {
                foreach ($this->data as $key => $value) {
                    if ($resource->set($key, $value, 0, $this->expire)) {
                        ++$this->count;
                    } else {
                        $this->response = false;
                    }
                }
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
        $this->getResponse();
        return $this->count;
    }

    /**
     * @inheritdoc
     */
    public function getLastId()
    {
    }
}
