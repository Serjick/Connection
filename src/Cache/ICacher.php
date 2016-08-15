<?php

namespace Imhonet\Connection\Cache;

use Imhonet\Connection\Query;

interface ICacher
{
    const TYPE_DATA = 'data';
    const TYPE_COUNT = 'count';
    const TYPE_COUNT_TOTAL = 'count_total';

    /**
     * @param string $key
     * @return bool
     */
    public function isCached($key);

    /**
     * @param string $key
     * @return bool
     */
    public function isLocked($key);

    /**
     * @param string $key
     * @return mixed
     */
    public function get($key);

    /**
     * @param string $key
     * @param mixed $data
     * @param array $tags
     * @param int|null $expire
     * @return bool
     */
    public function set($key, $data, $tags = array(), $expire = null);

    /**
     * @param string[] $keys
     * @return bool
     */
    public function lock($keys);

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
