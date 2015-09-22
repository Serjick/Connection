<?php

namespace Imhonet\Connection\Resource;

class Sphinx implements IResource
{
    /**
     * @var \SphinxClient
     */
    private $resource;

    private $host;
    private $port;

    public function __construct()
    {
        //for sphinx constants autoload
        class_exists('\\SphinxClient');
    }

    /**
     * @inheritdoc
     */
    public function getHandle()
    {
        if (!$this->resource) {
            $this->resource = new \SphinxClient();
            $this->resource->SetServer($this->host, $this->port);
        }

        return $this->resource;
    }


    /**
     * @inheritdoc
     */
    public function disconnect()
    {
        $this->resource = null;
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
}
