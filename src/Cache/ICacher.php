<?php

namespace Imhonet\Connection\Cache;

use Imhonet\Connection\Resource\IResource;
use Imhonet\Connection\Query;

interface ICacher
{
    const TYPE_DATA = 'data';
    const TYPE_VALUE = 'value';
    const TYPE_COUNT = 'count';
    const TYPE_COUNT_TOTAL = 'count_total';

    /**
     * @param mixed $object
     * @return bool
     */
    public function isCacheable($object);

    /**
     * @param string $key
     * @return bool
     */
    public function isCached($key);

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param
     * @return int|null
     */
    public function set($key, $data, $tags = array(), $expire = null);

    /**
     * @param
     * @return int|null
     */
    public function lock($key);

    /**
     * @param string[] $tags
     * @return int|null
     */
    public function dropTags($tags);

    /**
     * @param string[] $keys
     * @return bool
     */
    public function load($keys);
}
