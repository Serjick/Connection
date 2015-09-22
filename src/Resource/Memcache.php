<?php

namespace Imhonet\Connection\Resource;

class Memcache implements IResource
{
    const DELIMITER = ';';
    /**
     * @var \Memcache
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
            $this->resource = new \Memcache();

            foreach ($this->getServers() as $server) {
                $this->resource->addServer($server['host'], $server['port'], $this->isPersistent());
            }
        }

        return $this->resource;
    }

    private function isPersistent()
    {
        return defined('\CACHE_PERSISTENT') && constant('\CACHE_PERSISTENT');
    }

    private function getServers()
    {
        $result = array();
        $hosts = explode(self::DELIMITER, $this->host);
        $ports = explode(self::DELIMITER, $this->port);

        foreach ($hosts as $i => $host) {
            if (isset($ports[$i])) {
                $result[] = array(
                    'host' => $host,
                    'port' => (int) $ports[$i]
                );
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
            $this->resource->close();
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

    public function __destruct()
    {
        $this->disconnect();
    }
}
