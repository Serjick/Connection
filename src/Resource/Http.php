<?php

namespace Imhonet\Connection\Resource;

class Http implements IResource
{
    /**
     * @var resource
     */
    private $resource;

    /**
     * @inheritdoc
     */
    public function getHandle()
    {
        if (!$this->resource) {
            $this->resource = curl_init();
            curl_setopt_array($this->resource, array(
                \CURLOPT_RETURNTRANSFER => true,
            ));
        }

        return $this->resource;
    }

    /**
     * @inheritdoc
     */
    public function disconnect()
    {
        if ($this->resource) {
            curl_close($this->resource);
            $this->resource = null;
        }
    }

    /**
     * @inheritdoc
     */
    public function setHost($host)
    {
    }

    /**
     * @inheritdoc
     */
    public function setPort($port)
    {
    }

    /**
     * @inheritdoc
     */
    public function setUser($user)
    {
    }

    /**
     * @inheritdoc
     */
    public function setPassword($password)
    {
    }

    /**
     * @inheritdoc
     */
    public function setDatabase($database)
    {
    }

    /**
     * @inheritdoc
     */
    public function setTable($table)
    {
    }

    /**
     * @inheritdoc
     */
    public function setIndexName($name)
    {
    }

    /**
     * @inheritdoc
     */
    public function setIndexFields(array $fields)
    {
    }

    /**
     * @inheritdoc
     */
    public function setIds($ids)
    {
    }

    /**
     * @inheritdoc
     */
    public function getHost()
    {
    }

    /**
     * @inheritdoc
     */
    public function getPort()
    {
    }

    /**
     * @inheritdoc
     */
    public function getUser()
    {
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
    }

    /**
     * @inheritdoc
     */
    public function getDatabase()
    {
    }

    /**
     * @inheritdoc
     */
    public function getTable()
    {
    }

    /**
     * @inheritdoc
     */
    public function getIndexName()
    {
    }

    /**
     * @inheritdoc
     */
    public function getIndexFields()
    {
        //
    }

    /**
     * @inheritdoc
     */
    public function getIds()
    {
    }
}
