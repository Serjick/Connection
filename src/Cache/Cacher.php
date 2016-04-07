<?php

namespace Imhonet\Connection\Cache;

use Imhonet\Connection\Resource\IResource;
use Imhonet\Connection\Query\Memcached;
use Imhonet\Connection\Query;

class Cacher implements ICacher
{
    const DATA_EXPIRE = 31536000; // год
    const TAGS_EXPIRE = 31536000;
    const DEFAULT_EXPIRE = 600;

    private $errors = array();
    private $cached_data = array();
    private $cached_keys = array();
    private $cached_tags = array();

    private $main_resource;
    private $tags_resource;

    private $expire = null;
    private $tags = array();

    public static function instanceCacherMemcache($host, $port, $tags_host = null, $tags_port = null)
    {
        if ($tags_host === null || $tags_port === null) {
            $tags_host = $host;
            $tags_port = $port;
        }

        $resource = \Imhonet\Connection\Resource\Factory::getInstance()
            ->setHost($host)
            ->setPort($port)
            ->getResource(\Imhonet\Connection\Resource\Factory::TYPE_MEMCACHED);

        $tags_resource = \Imhonet\Connection\Resource\Factory::getInstance()
            ->setHost($tags_host)
            ->setPort($tags_port)
            ->getResource(\Imhonet\Connection\Resource\Factory::TYPE_MEMCACHED);

        return new Cacher($resource, $tags_resource);
    }

    public function __construct(IResource $main_resource, IResource $tag_resource)
    {
        $this->main_resource = $main_resource;
        $this->tags_resource = $tag_resource;
    }

    /**
     * @param mixed $object
     * @return bool
     */
    public function isCacheable($object)
    {
        return $object instanceof ICachable;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function isCached($key)
    {
        if (!array_key_exists($key, $this->cached_keys)) {
            $this->_load(array($key => $key));
        }

        return array_key_exists($key, $this->cached_data);
    }

    /**
     * @param string $key
     * @return mixed
     * @throws CacheException
     */
    public function get($key)
    {
        if (!array_key_exists($key, $this->cached_keys)) {
            $this->_load(array($key => $key));
        }

        if (array_key_exists($key, $this->errors)) {
            throw new CacheException($this->errors[$key]);
        }

        return array_key_exists($key, $this->cached_data) ? $this->cached_data[$key] : null;
    }

    /**
     * @param
     * @return int|null
     */
    public function set($key, $data, $tags = array(), $expire = null)
    {
        $this->lock($key, $tags, $expire);

        $set_query = $this->createSetQuery($this->main_resource);
        $set_query->setData(array($this->generateDataKey($key) => array($data)));
        $set_query->setExpire(time() + Cacher::DATA_EXPIRE);
        $set_query->execute();

        $this->cached_data[$key] = $data;
        $this->cached_keys[$key] = $key;
    }


    /**
     * @param
     * @return int|null
     */
    public function lock($key, $tags = array(), $expire = null)
    {
        if ($expire === null) {
            $expire = $this->expire !== null ? $this->expire : Cacher::DEFAULT_EXPIRE;
        }

        if (empty($tags)) {
            $tags = $this->tags;
        }

        $cache_stale = array($this->generateStaleKey($key) => array('tags' => $tags, 'timestamp' => time()));

        $set_query = $this->createSetQuery($this->main_resource);
        $set_query->setData($cache_stale);
        $set_query->setExpire(time() + $expire);
        $set_query->execute();

        $get_tags = array();
        $new_tags = array();

        foreach ($tags as $tag) {
            if (!array_key_exists($tag, $this->cached_tags)) {
                $get_tags[$tag] = $this->generateTagKey($tag);
            }
        }

        if (!empty($get_tags)) {
            $get_tags_query = $this->createGetQuery($this->tags_resource);
            $get_tags_query->setKeys($get_tags);
            $get_tags_data = $get_tags_query->execute();

            foreach ($get_tags as $tag => $tag_key) {
                if (array_key_exists($tag_key, $get_tags_data)) {
                    $this->cached_tags[$tag] = $get_tags_data[$tag_key];
                    unset($get_tags[$tag]);
                } else {
                    $timestamp = time();
                    $this->cached_tags[$tag] = $timestamp;
                    $new_tags[$tag_key] = $timestamp;
                }
            }

            if (!empty($new_tags)) {
                $set_tags_query = $this->createSetQuery($this->tags_resource);
                $set_tags_query->setData($new_tags);
                $set_tags_query->setExpire(time() + Cacher::TAGS_EXPIRE);
                $set_tags_query->execute();
            }
        }
    }


    /**
     * @param array $tags
     * @return int|null
     */
    public function dropTags($tags)
    {
        $timestamp = time();
        $tags_data = array();

        foreach ($tags as $tag) {
            if (array_key_exists($tag, $this->cached_tags)) {
                $this->cached_tags[$tag] = $timestamp;
            }

            $tags_data[$this->generateTagKey($tag)] = $timestamp;
        }

        $set_tags_query = $this->createSetQuery($this->tags_resource);
        $set_tags_query->setData($tags_data);
        $set_tags_query->setExpire(Cacher::TAGS_EXPIRE);
        $set_tags_query->execute();
    }

    /**
     * @param array $kags
     * @return int|null
     */
    public function dropKeys($keys)
    {
        $keys_data = array();

        foreach ($keys as $key) {
            if (array_key_exists($key, $this->cached_data)) {
                unset($this->cached_data[$key]);
            }

            $keys_data[$this->generateStaleKey($key)] = array('timestamp' => 0);
        }

        $set_query = $this->createSetQuery($this->main_resource);
        $set_query->setData($keys_data);
        $set_query->setExpire(time());
        $set_query->execute();
    }

    /**
     * @param string[] $keys
     * @return bool
     */
    public function load($keys)
    {
        $this->_load(array_diff_assoc(array_combine($keys, $keys), $this->cached_keys));
    }

    private function _load($keys)
    {
        if (!empty($keys)) {
            $this->cached_keys = array_merge($this->cached_keys, $keys);

            $cache_keys = array();

            foreach ($keys as $key) {
                $cache_keys[] = $this->generateStaleKey($key);
                $cache_keys[] = $this->generateDataKey($key);
            }

            $get_query = $this->createGetQuery($this->main_resource);
            $get_query->setKeys($cache_keys);
            $cached_data = $get_query->execute();

            $cached_tags = array();

            foreach ($keys as $key) {
                if ($this->validateCacheData($key, $cached_data)) {
                    foreach ($cached_data[$this->generateStaleKey($key)]['tags'] as $tag) {
                        if (!array_key_exists($tag, $this->cached_tags)) {
                            $cached_tags[$tag] = $this->generateTagKey($tag);
                        }
                    }
                } else {
                    if ($this->validateCacheStale($key, $cached_data)) {
                        $this->errors[$key] = 'Race condition exception!';
                    }

                    unset($keys[$key]);
                }
            }

            if (!empty($keys)) {
                if (!empty($cached_tags)) {
                    $get_tags_query = $this->createGetQuery($this->tags_resource);
                    $get_tags_query->setKeys($cached_tags);
                    $tags_data = $get_tags_query->execute();

                    foreach ($cached_tags as $tag => $nop) {
                        if (array_key_exists($this->generateTagKey($tag), $tags_data)) {
                            $this->cached_tags[$tag] = $tags_data[$this->generateTagKey($tag)];
                        }
                    }
                }

                foreach ($keys as $key) {
                    if ($this->validateCacheTags($key, $cached_data)) {
                        $this->cached_data[$key] = $cached_data[$this->generateDataKey($key)];
                    }
                }
            }
        }
    }

    private function validateCacheTags($key, $cached_data)
    {
        foreach ($cached_data[$this->generateStaleKey($key)]['tags'] as $tag) {
            if (!array_key_exists($tag, $this->cached_tags) || $cached_data[$this->generateStaleKey($key)]['timestamp'] < $this->cached_tags[$tag]) {
                return false;
            }
        }

        return true;
    }

    private function validateCacheData($key, $cached_data)
    {
        return array_key_exists($this->generateStaleKey($key), $cached_data) && array_key_exists($this->generateDataKey($key), $cached_data);
    }

    private function validateCacheStale($key, $cached_data)
    {
        return array_key_exists($this->generateStaleKey($key), $cached_data) && $cached_data[$this->generateStaleKey($key)]['timestamp'] + 1 >= time();
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
        $this->expire = (int)$expire;
        return $this;
    }

    public function setTags(array $tags)
    {
        $this->tags = $tags;
        return $this;
    }

    protected function createGetQuery($resource)
    {
        return (new Memcached\Get())->setResource($resource);
    }

    protected function createSetQuery($resource)
    {
        return (new Memcached\Set())->setResource($resource);
    }
}
