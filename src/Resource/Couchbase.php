<?php

namespace Imhonet\Connection\Resource;

class Couchbase implements IResource
{
    /**
     * @var \Couchbase
     */
    private $resource;

    private $host;
    private $port;
    private $user;
    private $password;
    private $bucket;

    /**
     * @inheritdoc
     */
    public function getHandle()
    {
        if (!$this->resource) {
            $this->resource = new \Couchbase(
                $this->host . ':' . $this->port,
                $this->user,
                $this->password,
                $this->bucket
            );
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
        $this->user = $user;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function setDatabase($database)
    {
        $this->bucket = $database;

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
     * @inheritdoc
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @inheritdoc
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @inheritdoc
     */
    public function getDatabase()
    {
        return $this->bucket;
    }
}
