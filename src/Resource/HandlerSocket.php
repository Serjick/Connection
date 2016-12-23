<?php

namespace Imhonet\Connection\Resource;

use HSPHP\ReadSocket;

class HandlerSocket implements IResource
{
    const DEFAULT_PORT = 9998;

    /**
     * @var ReadSocket
     */
    private $resource;

    private $host;
    private $port = self::DEFAULT_PORT;
    private $db;

    /**
     * @inheritdoc
     */
    public function getHandle()
    {
        if (!$this->resource) {
            $this->resource = new ReadSocket();
            $this->resource->connect($this->host, $this->port);
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
     * @inheritdoc
     */
    public function setPort($port)
    {
        $this->port = $port;

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
     * @inheritdoc
     */
    public function getPort()
    {
        return $this->port;
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
