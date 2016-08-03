<?php

namespace Imhonet\Connection;

use Imhonet\Connection\DataFormat\IDataFormat;
use Imhonet\Connection\DataFormat\IMulti;
use Imhonet\Connection\Query\IQuery;
use Imhonet\Connection\Resource\IResource;
use Imhonet\Connection\Cache\ICacher;
use Imhonet\Connection\Cache\ICacheable;
use Imhonet\Connection\Cache\CacheException;

class Request implements \Iterator, IErrorable
{
    /**
     * @var IQuery
     */
    private $query;

    /**
     * @var IResource
     */
    protected $resource;

    /**
     * @var IDataFormat|IMulti
     */
    private $format;
    private $is_format_ready = false;

    private $response;
    private $has_response = false;
    private $cache_loaded = false;

    private $is_valid_iteration = true;

    /**
     * @var ICacher
     */
    private $cacher = null;
    private $error = array();

    /**
     * @param IQuery $query
     * @param IDataFormat|IMulti $format
     */
    public function __construct(IQuery $query, IDataFormat $format)
    {
        $this->query = $query;
        $this->format = $format;
    }

    /**
     * @return $this
     */
    public function execute()
    {
        $this->getResponse();

        return $this;
    }

    public function repeat()
    {
        assert($this->hasResponse() === true);
        $this->getResponse(false);

        return $this;
    }

    /**
     * @param bool $cached
     * @return mixed
     */
    private function getResponse($cached = true)
    {
        if ($this->cacher !== null && $this->cache_loaded === false) {
            $this->loadCache();
            $this->cache_loaded = true;
        }

        if (!$cached || !$this->hasResponse()) {
            $this->response = $this->query->execute();
            $this->has_response = true;
        }

        return $this->response;
    }

    /**
     * @return bool
     */
    private function hasResponse()
    {
        return $this->has_response;
    }

    /**
     * @return array|\Traversable
     */
    public function getData()
    {
        try {
            if ($this->cacher !== null && $this->cacher->isCacheable($this->format) && $this->cacher->isCached($this->generateCacheKey($this->query, $this->format))) {
                $result = $this->cacher->get($this->generateCacheKey($this->query, $this->format));
            } else {
                if ($this->cacher !== null && $this->cacher->isCacheable($this->format)) {
                    $this->cacher->lock($this->generateCacheKey($this->query, $this->format));
                }

                $result = $this->getFormater()->formatData();

                if ($this->cacher !== null && $this->cacher->isCacheable($this->format)) {
                    $this->cacher->set($this->generateCacheKey($this->query, $this->format), $result, $this->query->getTags(), $this->query->getExpire());
                }
            }
        } catch (CacheException $e) {
            $result = null;
            $this->error[$this->generateCacheKey($this->query, $this->format)] = IQuery::STATUS_TEMPORARY_UNAVAILABLE;
        }

        $this->query->seek($this->key());

        return $result;
    }

    /**
     * @return float|int|null|string
     */
    public function getValue()
    {
        try {
            if ($this->cacher !== null && $this->cacher->isCacheable($this->format) && $this->cacher->isCached($this->generateCacheKey($this->query, $this->format))) {
                $result = $this->cacher->get($this->generateCacheKey($this->query, $this->format));
            } else {
                if ($this->cacher !== null && $this->cacher->isCacheable($this->format)) {
                    $this->cacher->lock($this->generateCacheKey($this->query, $this->format));
                }

                $result = $this->getFormater()->formatValue();

                if ($this->cacher !== null && $this->cacher->isCacheable($this->format)) {
                    $this->cacher->set($this->generateCacheKey($this->query, $this->format), $result, $this->query->getTags(), $this->query->getExpire());
                }
            }
        } catch (CacheException $e) {
            $result = null;
            $this->error[$this->generateCacheKey($this->query, $this->format)] = IQuery::STATUS_TEMPORARY_UNAVAILABLE;
        }

        $this->query->seek($this->key());

        return $result;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        if (array_key_exists($this->generateCacheKey($this->query, $this->format), $this->error)) { //cache error
            return $this->error;
        }

        return $this->query->getErrorCode() | ($this->isFormaterErrorable() ? $this->getFormater()->getErrorCode() : 0);
    }

    /**
     * @return int
     */
    public function getCount()
    {
        try {
            if ($this->cacher !== null && $this->cacher->isCached($this->generateCacheKey($this->query, $this->format, ICacher::TYPE_COUNT))) {
                $result = $this->cacher->get($this->generateCacheKey($this->query, $this->format, ICacher::TYPE_COUNT));
            } else {
                if ($this->cacher !== null && $this->cacher->isCacheable($this->format)) {
                    $this->cacher->lock($this->generateCacheKey($this->query, $this->format, ICacher::TYPE_COUNT), $this->query->getTags(), $this->query->getExpire());
                }

                $result = $this->query->getCount();

                if ($this->cacher !== null && $this->cacher->isCacheable($this->format)) {
                    $this->cacher->set($this->generateCacheKey($this->query, $this->format, ICacher::TYPE_COUNT), $result, $this->query->getTags(), $this->query->getExpire());
                }
            }
        } catch (CacheException $e) {
            $result = null;
            $this->error[$this->generateCacheKey($this->query, $this->format)] = IQuery::STATUS_TEMPORARY_UNAVAILABLE;
        }

        return $result;
    }

    /**
     * @return int|null
     */
    public function getCountTotal()
    {
        try {
            if ($this->cacher !== null && $this->cacher->isCached($this->generateCacheKey($this->query, $this->format, ICacher::TYPE_COUNT_TOTAL))) {
                $result = $this->cacher->get($this->generateCacheKey($this->query, $this->format, ICacher::TYPE_COUNT_TOTAL));
            } else {
                if ($this->cacher !== null && $this->cacher->isCacheable($this->format)) {
                    $this->cacher->lock($this->generateCacheKey($this->query, $this->format, ICacher::TYPE_COUNT_TOTAL), $this->query->getTags(), $this->query->getExpire());
                }

                $result = $this->query->getCountTotal();

                if ($this->cacher !== null && $this->cacher->isCacheable($this->format)) {
                    $this->cacher->set($this->generateCacheKey($this->query, $this->format, ICacher::TYPE_COUNT_TOTAL), $result, $this->query->getTags(), $this->query->getExpire());
                }
            }
        } catch (CacheException $e) {
            $result = null;
            $this->error[$this->generateCacheKey($this->query, $this->format)] = IQuery::STATUS_TEMPORARY_UNAVAILABLE;
        }

        return $result;
    }

    /**
     * @return int|null
     */
    public function getLastId()
    {
        return $this->query->getLastId();
    }

    /**
     * @return IDataFormat|IMulti|IErrorable
     */
    private function getFormater()
    {
        if (!$this->is_format_ready) {
            $this->format = $this->prepareFormat();
            $this->is_format_ready = true;
        }

        return $this->format;
    }

    private function prepareFormat()
    {
        return $this->format->setData($this->getResponse());
    }

    private function isFormaterIterable()
    {
        return $this->format instanceof IMulti;
    }

    private function isFormaterErrorable()
    {
        return $this->format instanceof IErrorable;
    }

    /**
     * @inheritdoc
     */
    public function current()
    {
        return $this->getData();
    }

    /**
     * @inheritdoc
     */
    public function next()
    {
        $this->is_valid_iteration = $this->isFormaterIterable() ? $this->getFormater()->moveNext() : false;
    }

    /**
     * @inheritdoc
     */
    public function key()
    {
        return $this->isFormaterIterable() ? $this->getFormater()->getIndex() : 0;
    }

    /**
     * @inheritdoc
     */
    public function valid()
    {
        return $this->is_valid_iteration;
    }

    /**
     * @inheritdoc
     */
    public function rewind()
    {
        assert($this->is_valid_iteration, 'Repeated iterations not supported');
    }

    /**
     * @inheritDoc
     */
    public function __debugInfo()
    {
        return array(
            'query' => $this->query->getDebugInfo(),
            'error' => $this->query->getDebugInfo(IQuery::INFO_TYPE_ERROR),
        );
    }

    /**
     * @param ICacher $cacher
     */
    public function setCacher(ICacher $cacher)
    {
        $this->cacher = $cacher;
    }

    private function generateCacheKey(IQuery $query, IDataFormat $formater = null, $type = null)
    {
        $key = 'query_' . $query->getCacheKey();

        if ($formater !== null && $this->cacher->isCacheable($formater)) {
            $key = $key . '_formater_' . $formater->getCacheKey();
        }

        if ($type !== null) {
            $key = $key . '_' . $type;
        }

        return $key;
    }

    private function generateCacheKeys(IQuery $query, IDataFormat $formater = null)
    {
        $keys = array();

        $current_query_position = $query->key();

        $query->rewind();

        while ($query->valid()) {
            $tags = $query->getTags();

            $keys[$this->generateCacheKey($query, $formater)] = $tags;
            $keys[$this->generateCacheKey($query, $formater, ICacher::TYPE_COUNT)] = $tags;
            $keys[$this->generateCacheKey($query, $formater, ICacher::TYPE_COUNT_TOTAL)] = $tags;

            $query->next();
        }

        $query->seek($current_query_position);

        return $keys;
    }

    public function loadCache()
    {
        $this->cacher->load($this->generateCacheKeys($this->query, $this->format));

        $current_query_position = $this->query->key();
        $this->query->rewind();

        while ($this->query->valid()) {
            if ($this->cacher->isCacheable($this->format) && $this->cacher->isCached($this->generateCacheKey($this->query, $this->format))) {
                $this->query->disableQuery();
            }

            $this->query->next();
        }

        $this->query->seek($current_query_position);
    }
}
