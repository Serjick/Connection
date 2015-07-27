<?php

namespace Imhonet\Connection\Resource;

class Memcached implements IResource
{
    const DELIMITER = ';';
    /**
     * @var \Memcached
     */
    private $resource;

    private $host;
    private $port;

    /**
     * @inheritdoc
     */
    public function getHandle()
    {
        if (!$this->resource) {
            $this->resource = $this->isPersistent() ? new \Memcached($this->getPersistentId()) : new \Memcached();

            if (!$this->resource->getServerList()) {
                $this->resource->addServers($this->getServers());
            }

            if ($options = $this->getOptions()) {
                $this->resource->setOptions($options);
            }
        }

        return $this->resource;
    }

    private function isPersistent()
    {
        return defined('\CACHE_PERSISTENT') && constant('\CACHE_PERSISTENT');
    }

    private function getPersistentId()
    {
        return md5($this->host . self::DELIMITER . $this->port);
    }

    private function getOptions()
    {
        $options = array();

        if (defined('\MEMCACHED_SERIALIZER')) {
            $serializer = constant('\MEMCACHED_SERIALIZER');
            assert($serializer != \Memcached::SERIALIZER_IGBINARY || \Memcached::HAVE_IGBINARY);
            assert($serializer != \Memcached::SERIALIZER_JSON || \Memcached::HAVE_JSON);
            $options[\Memcached::OPT_SERIALIZER] = $serializer;
        }

        if (defined('\MEMCACHED_BINARY')) {
            $options[\Memcached::OPT_BINARY_PROTOCOL] = constant('\MEMCACHED_BINARY');
        }

        if (defined('\MEMCACHED_ASYNC')) {
            $options[\Memcached::OPT_NO_BLOCK] = constant('\MEMCACHED_ASYNC');
        }

        if (defined('\MEMCACHED_TCP_NODELAY')) {
            $options[\Memcached::OPT_TCP_NODELAY] = constant('\MEMCACHED_TCP_NODELAY');
        }

        return $options;
    }

    private function getServers()
    {
        $result = array();
        $hosts = explode(self::DELIMITER, $this->host);
        $ports = explode(self::DELIMITER, $this->port);

        foreach ($hosts as $i => $host) {
            if (isset($ports[$i])) {
                $result[] = array($host, $ports[$i]);
            }
        }

        return $result;
    }
    /**
     * @inheritdoc
     */
    public function disconnect()
    {
        if ($this->resource) {
            $this->resource->quit();
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
    }

    /**
     * @inheritdoc
     */
    public function getIds()
    {
    }

    public function __destruct()
    {
        $this->disconnect();
    }
}
