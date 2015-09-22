<?php

namespace Imhonet\Connection\Resource;

use HSPHP\ReadSocket;

class HandlerSocket implements IResource
{
    /**
     * @var ReadSocket
     */
    private $resource;

    private $host;
    private $db;

    /**
     * @inheritdoc
     */
    public function getHandle()
    {
        if (!$this->resource) {
            $this->resource = new ReadSocket();
            $this->resource->connect($this->host);
        }

        return $this->resource;
    }

    /**
     * @inheritdoc
     */
    public function disconnect()
    {
        if ($this->resource) {
            $this->resource->disconnect();
            $this->resource = null;
        }
    }

    /**
     * @inheritdoc
     */
    public function setHost($host)
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @deprecated
     * @inheritdoc
     */
    public function setPort($port)
    {
        return $this;
    }

    /**
     * @deprecated
     * @inheritdoc
     */
    public function setUser($user)
    {
        return $this;
    }

    /**
     * @deprecated
     * @inheritdoc
     */
    public function setPassword($password)
    {
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDatabase($database)
    {
        $this->db = $database;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @deprecated
     * @inheritdoc
     */
    public function getPort()
    {
    }

    /**
     * @deprecated
     * @inheritdoc
     */
    public function getUser()
    {
    }

    /**
     * @deprecated
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
        return $this->db;
    }
}
