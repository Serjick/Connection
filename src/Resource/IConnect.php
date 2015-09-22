<?php

namespace Imhonet\Connection\Resource;

/**
 * Интерфейс коннекта
 */
interface IConnect
{
    /**
     * @param string $host
     * @return self
     */
    public function setHost($host);

    /**
     * @param string|int $port
     * @return self
     */
    public function setPort($port);

    /**
     * @param string $user
     * @return self
     */
    public function setUser($user);

    /**
     * @param string $password
     * @return self
     */
    public function setPassword($password);

    /**
     * @param string $database
     * @return self
     */
    public function setDatabase($database);

    /**
     * @return string
     */
    public function getHost();

    /**
     * @return string|int
     */
    public function getPort();

    /**
     * @return string
     */
    public function getUser();

    /**
     * @return string
     */
    public function getPassword();

    /**
     * @return string
     */
    public function getDatabase();
}
