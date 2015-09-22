<?php

namespace Imhonet\Connection\Resource\PDO;

use Imhonet\Connection\Resource\IResource;

abstract class PDO implements IResource
{
    /**
     * @var \PDO
     */
    private $resource;

    private $host;
    private $port;
    private $user;
    private $password;
    private $database;

    /**
     * @inheritdoc
     */
    public function getHandle()
    {
        if (!$this->resource) {
            $this->resource = $this->connect();

            foreach ($this->getAttributes() as $attr => $value) {
                $this->resource->setAttribute($attr, $value);
            }
        }

        return $this->resource;
    }

    protected function connect()
    {
        return new \PDO($this->getDSN(), $this->user, $this->password, array(
            \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES UTF8'
        ));
    }

    /**
     * @return string
     */
    private function getDSN()
    {
        $dsn = $this->getEngine() . ':host=' . $this->host
            . ';dbname=' . $this->database
            . ';port=' . $this->port
            . ';charset=UTF8'
        ;

        return $dsn;
    }

    abstract protected function getEngine();

    protected function getAttributes()
    {
        return array(
            \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
            \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
            //\PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => false,
            //\PDO::ATTR_PERSISTENT => true,
        );
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
        $this->database = $database;

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
        return $this->database;
    }
}
