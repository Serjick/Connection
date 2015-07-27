<?php

namespace Imhonet\Connection\Resource;

/**
 * Интерфейс коннекта
 */
interface IConnect
{
    /**
     * @param  $host
     * @return self
     */
    public function setHost($host);

    /**
     * @param  $port
     * @return self
     */
    public function setPort($port);

    /**
     * @param  $user
     * @return self
     */
    public function setUser($user);

    /**
     * @param  $password
     * @return self
     */
    public function setPassword($password);

    /**
     * @param  $database
     * @return self
     */
    public function setDatabase($database);

    /**
     * @param  $table
     * @return self
     */
    public function setTable($table);

    /**
     * @param  $name
     * @return self
     */
    public function setIndexName($name);

    /**
     * @param  array $fields
     * @return self
     */
    public function setIndexFields(array $fields);

    /**
     * @param  array $ids
     * @return self
     */
    public function setIds($ids);

    /**
     * @return string
     */
    public function getHost();

    /**
     * @return string
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

    /**
     * @return string
     */
    public function getTable();

    /**
     * @return string
     */
    public function getIndexName();

    /**
     * @return array
     */
    public function getIndexFields();

    /**
     * @return array
     */
    public function getIds();
}
