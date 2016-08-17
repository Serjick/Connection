<?php

namespace Imhonet\Connection\Cache;

use Imhonet\Connection\Query\IQuery;
use Imhonet\Connection\DataFormat\IDataFormat;
use Imhonet\Connection\DataFormat\IDecorator;

/**
 * @property ICachable $format
 * @property IQuery|IQuery[] $query
 * @property int[] $error
 */
trait TCache
{
    /**
     * @var ICacher
     */
    private $cacher;
    private $cache_loaded = false;

    /**
     * @type IDataFormat|IDecorator|ICachable
     */
    private $format_cache;

    /**
     * @param ICacher $cacher
     * @param IDataFormat|IDecorator|ICachable $format
     * @return $this|self
     */
    public function setCacher(ICacher $cacher, IDataFormat $format)
    {
        $this->cacher = $cacher;

        assert($format instanceof IDecorator);
        assert($format instanceof ICachable);
        $this->format_cache = $format;

        return $this;
    }

    private function prepareCache()
    {
        $locks = [];

        foreach ($this->getQuery() as $query) {
            if (!$this->isCachedQuery($query)) {
                $locks[] = $this->generateCacheKey($query);
            }
        }

        if ($locks) {
            $result = $this->cacher->lock($locks);
            assert($result === true, 'Cache locks could not be set');
        }
    }

    /**
     * @param IQuery $query
     * @return IQuery
     */
    private function prepareQueryCache(IQuery &$query)
    {
        if (!$this->cache_loaded) {
            $this->cacher->load($this->getCacheKeys());
            $this->cache_loaded = true;
        }

        $key = $this->generateCacheKey($query);
        $query->disable($this->cacher->isCached($key) || $this->cacher->isLocked($key));

        return $query;
    }

    /**
     * @return IDataFormat|IDecorator|ICachable
     */
    private function prepareFormatCache()
    {
        $data = [];
        foreach ($this->getQuery() as $query_id => $query) {
            if ($this->isCachedQuery($query)) {
                $key = $this->generateCacheKey($query);

                try {
                    $value = $this->cacher->get($key);
                } catch (CacheException $e) {
                    $value = $e;
                }

                $data[$query_id] = $value;
            }
        }

        return $this->format_cache->setData($data);
    }

    /**
     * @todo atomic save of multi query
     * @todo data, count and count_total caches sync
     * @param \Closure $f
     * @return \Closure
     */
    private function decorateDataCache(\Closure $f)
    {
        if ($this->isCachable()) {
            $f = function () use ($f) {
                $result = $f();
                $query = $this->query->current();

                if (!$this->isCachedQuery($query)) {
                    $key = $this->generateCacheKey($query);
                    $success = $this->cacher->set($key, $result, $query->getCacheTags(), $query->getCacheExpire());
                    assert($success === true);
                }

                return $result;
            };
        }

        return $f;
    }

    /**
     * @todo data, count and count_total caches sync
     * @todo atomic multi lock at first call for multi queries
     * @param \Closure $f
     * @param string $type
     * @return \Closure
     */
    private function decorateCountCache(\Closure $f, $type = ICacher::TYPE_COUNT)
    {
        if ($this->isCachable()) {
            $f = function () use ($f, $type) {
                $key = $this->generateCacheKey($this->query->current(), $type);

                if ($this->cacher->isCached($key)) {
                    try {
                        $result = $this->cacher->get($key);
                    } catch (CacheException $e) {
                        $this->error[ $this->query->key() ] = $e;
                        $result = null;
                    }
                } else {
                    $this->cacher->lock([$key]);
                    $result = $f();
                    $success = $this->cacher->set($key, $result, $this->query->getCacheTags(), $this->query->getCacheExpire());
                    assert($success === true);
                }

                return $result;
            };
        }

        return $f;
    }

    /**
     * @param IQuery $query
     * @param string $type
     * @return string|null
     */
    private function generateCacheKey(IQuery $query, $type = ICacher::TYPE_DATA)
    {
        return $this->isCachable()
            ? 'query_' . $query->getCacheKey() . '_formater_' . $this->format->getCacheKey() . '_' . $type
            : null;
    }

    /**
     * @return string[]
     */
    private function getCacheKeys()
    {
        $keys = array();

        foreach ($this->query as $query) {
            $tags = $query->getCacheTags();
            $keys[ $this->generateCacheKey($query) ] = $tags;
            $keys[ $this->generateCacheKey($query, ICacher::TYPE_COUNT) ] = $tags;
            $keys[ $this->generateCacheKey($query, ICacher::TYPE_COUNT_TOTAL) ] = $tags;
        }

        return $keys;
    }

    private function isCachable()
    {
        return $this->cacher && $this->format instanceof ICachable;
    }

    private function isCachedQuery(IQuery $query)
    {
        return $query->disable(null);
    }

    /**
     * @return IQuery[]
     */
    abstract protected function getQuery();
    abstract public function getErrorCode();
}
