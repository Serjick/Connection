<?php

namespace Imhonet\Connection\Cache;

use Imhonet\Connection\Query;
use Imhonet\Connection\Cache\QueryStrategy\IQueryStrategy;

class Cacher implements ICacher
{
    const EXPIRE_DEFAULT = 600;
    const EXPIRE_LOCK = 0.5;

    const ERR_RACE_CONDITION = 'Race condition exception!';

    private $errors = array();
    private $cached_data = array();
    private $cached_tags = array();

    /**
     * @type IQueryStrategy
     */
    private $storage;
    /**
     * @type IQueryStrategy
     */
    private $storage_tags;

    private $expire = self::EXPIRE_DEFAULT;
    private $tags_common = array();

    private $timestamp;


    public function __construct(IQueryStrategy $storage, IQueryStrategy $storage_tags = null)
    {
        $this->setStorages($storage, $storage_tags);
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isCached($key)
    {
        return array_key_exists($key, $this->cached_data);
    }

    public function isLocked($key)
    {
        return isset($this->errors[$key]) && $this->errors[$key] === self::ERR_RACE_CONDITION;
    }

    /**
     * @param string $key
     * @return mixed
     * @throws CacheException
     */
    public function get($key)
    {
        if (array_key_exists($key, $this->errors)) {
            throw new CacheException($this->errors[$key]);
        }

        return $this->isCached($key) ? $this->cached_data[$key] : null;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $data, $tags = array(), $expire = null)
    {
        $this->createStale([$key], $expire);
        $this->createTags($tags);
        $this->save(array($this->generateDataKey($key) => $data));

        return true;
    }

    /**
     * @todo errors handling
     * @inheritdoc
     */
    public function lock($keys)
    {
        return $this->createStale($keys, ceil(self::EXPIRE_LOCK));
    }

    /**
     * @param array $tags
     * @return int|null
     */
    public function dropTags($tags)
    {
        return $this->createTags($tags, true);
    }

    /**
     * @todo return false when failure
     * @param string[] $keys
     * @return bool
     */
    public function load($keys)
    {
        $keys = array_diff_key($keys, $this->cached_data);

        if (!empty($keys)) {
            $cache_keys = array();
            $cache_tags = array();

            foreach ($keys as $key => $tags) {
                $cache_keys[] = $this->generateStaleKey($key);
                $cache_keys[] = $this->generateDataKey($key);
                $cache_tags += array_flip($tags);
            }

            $this->loadTags(array_keys($cache_tags));
            $cached_data = $this->fetch($cache_keys);

            foreach ($keys as $key => $tags) {
                if ($this->validateCacheData($key, $tags, $cached_data)) {
                    $this->cached_data[$key] = $cached_data[ $this->generateDataKey($key) ];
                } elseif (!$this->validateCacheStale($key, $cached_data)) {
                    $this->errors[$key] = self::ERR_RACE_CONDITION;
                }
            }
        }

        return true;
    }

    private function loadTags($tags)
    {
        $keys = $this->tags_common;

        foreach ($tags as $tag) {
            if (!isset($this->cached_tags[$tag])) {
                $keys[$tag] = $this->generateTagKey($tag);
            }
        }

        if ($keys) {
            $cached_tags = $this->fetchTags($keys);

            $keys = array_flip($keys);
            foreach ($cached_tags as $tag_key => $tag_time) {
                $this->cached_tags[$keys[$tag_key]] = $tag_time;
            }
        }

        return $this->cached_tags;
    }

    private function validateCacheData($key, $tags, $cached_data)
    {
        $stale = $this->generateStaleKey($key);
        $result = isset($cached_data[$stale]) && array_key_exists($this->generateDataKey($key), $cached_data);

        foreach ($tags as $tag) {
            $result = $result && isset($this->cached_tags[$tag]) && $cached_data[$stale] >= $this->cached_tags[$tag];
        }

        foreach ($this->tags_common as $tag) {
            $result = $result && isset($this->cached_tags[$tag]) && $cached_data[$stale] >= $this->cached_tags[$tag];
        }

        return $result;
    }

    private function validateCacheStale($key, $cached_data)
    {
        return !array_key_exists($this->generateStaleKey($key), $cached_data)
            || $cached_data[ $this->generateStaleKey($key) ] + self::EXPIRE_LOCK < microtime(true)
        ;
    }

    private function generateStaleKey($key)
    {
        return $key . '__stale';
    }

    private function generateDataKey($key)
    {
        return $key . '__data';
    }

    private function generateTagKey($key)
    {
        return $key . '__tag';
    }

    public function setExpire($expire)
    {
        $this->expire = (int) $expire;

        return $this;
    }

    public function setTags(array $tags)
    {
        foreach ($tags as $tag) {
            $this->tags_common[$tag] = $this->generateTagKey($tag);
        }

        return $this;
    }

    /**
     * @param string[] $keys
     * @param int|null $expire
     * @return bool
     */
    private function createStale($keys, $expire = null)
    {
        $timestamp = $this->getTimestamp();
        $expire = $expire !== null ? $expire : $this->expire;
        $stales = [];

        foreach ($keys as $key) {
            $stale = $this->generateStaleKey($key);
            $stales[$stale] = $timestamp;
        }

        return $this->save($stales, $expire);
    }

    /**
     * @param string[] $tags
     * @param bool $update
     * @return bool
     */
    private function createTags($tags = array(), $update = false)
    {
        $result = true;
        $tags = array_flip($tags);
        $tags_all = $this->tags_common + $tags;
        $new_tags = array();

        if (!empty($tags_all)) {
            $get_tags_data = $this->loadTags(array_keys($tags_all));

            foreach ($tags_all as $tag => $tag_key) {
                if (!isset($get_tags_data[$tag]) || ($update && isset($tags[$tag]))) {
                    $new_tags[$tag] = $this->generateTagKey($tag);
                }
            }

            if (!empty($new_tags) && $result = $this->saveTags($new_tags)) {
                $this->cached_tags = array_fill_keys(array_keys($new_tags), $this->getTimestamp());
            }
        }

        return $result;
    }

    private function fetch(array $keys, IQueryStrategy $fetcher = null)
    {
        $fetcher = $fetcher ? : $this->storage;
        $query = $fetcher->createGetQuery();
        $query->setKeys($keys);

        return $query->execute();
    }

    private function save(array $data, $expire = null, IQueryStrategy $storage = null)
    {
        $storage = $storage ? : $this->storage;
        $set_query = $storage->createSetQuery();
        $set_query->setData($data);

        if ($expire !== null) {
            $set_query->setExpire(time() + $expire);
        }

        return $set_query->execute();
    }

    private function fetchTags(array $keys)
    {
        return $this->fetch($keys, $this->storage_tags);
    }

    private function saveTags(array $keys)
    {
        return $this->save(array_fill_keys($keys, $this->getTimestamp()), null, $this->storage_tags);
    }

    private function getTimestamp()
    {
        return $this->timestamp ? : $this->timestamp = microtime(true);
    }

    /**
     * @return IQueryStrategy
     */
    public function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return IQueryStrategy
     */
    public function getStorageTags()
    {
        return $this->storage_tags;
    }

    /**
     * @param IQueryStrategy $storage
     * @param IQueryStrategy $storage_tags
     * @return Cacher
     */
    public function setStorages(IQueryStrategy $storage, IQueryStrategy $storage_tags = null)
    {
        $this->storage = $storage;
        $this->storage_tags = $storage_tags ? $storage_tags : $storage;

        return $this;
    }
}
