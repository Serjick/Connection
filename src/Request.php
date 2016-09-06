<?php

namespace Imhonet\Connection;

use Imhonet\Connection\DataFormat\IDataFormat;
use Imhonet\Connection\DataFormat\IMulti;
use Imhonet\Connection\Query\IQuery;
use Imhonet\Connection\Resource\IResource;
use Imhonet\Connection\Cache\TCache;
use Imhonet\Connection\Cache\ICacher;
use Imhonet\Connection\Cache\ICachable;

class Request implements \Iterator, IErrorable
{
    use TCache;

    /**
     * @var IQuery|IQuery[]
     */
    private $query;
    private $is_query_ready = false;

    /**
     * @var IResource
     */
    protected $resource;

    /**
     * @var IDataFormat|IMulti|IErrorable|ICachable
     */
    private $format;
    private $is_format_ready = false;

    private $response;
    private $has_response = false;

    private $is_valid_iteration = true;

    private $error = array();

    /**
     * @param IQuery $query
     * @param IDataFormat|IMulti|IErrorable|ICachable $format
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
        if (!$cached || !$this->hasResponse()) {
            $this->beforeRequestData();
            $this->response = $this->getQuery()->execute();
            $this->has_response = true;
        }

        return $this->response;
    }

    private function beforeRequestData()
    {
        if ($this->isCachable()) {
            $this->prepareCache();
        }
    }

    /**
     * @todo support repeatable queries
     */
    protected function getQuery()
    {
        if (!$this->is_query_ready) {
            foreach ($this->query as $query) {
                if ($this->isCachable()) {
                    $this->prepareQueryCache($query);
                }
            }

            $this->is_query_ready = true;
        }

        return $this->query;
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
        $f = $this->decorateDataCache(function () {
            $result = $this->getFormater()->formatData();
            $this->query->seek($this->key());

            return $result;
        });

        return $f();
    }

    /**
     * @return float|int|null|string
     */
    public function getValue()
    {
        $f = $this->decorateDataCache(function () {
            $result = $this->getFormater()->formatValue();
            $this->query->seek($this->key());

            return $result;
        });

        return $f();
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        $f = $this->decorateErrorCodeCache(function () {
            return $this->getErrorCodeQuery() | ($this->getErrorCodeFormatter());
        });

        return $f();
    }

    private function getErrorCodeQuery()
    {
        return $this->query->getErrorCode();
    }

    protected function getErrorCodeFormatter()
    {
        return $this->isFormaterErrorable() ? $this->getFormater()->getErrorCode() : 0;
    }

    /**
     * @return int
     */
    public function getCount()
    {
        $f = $this->decorateCountCache(function () {
            return $this->query->getCount();
        });

        return $f();
    }

    /**
     * @return int|null
     */
    public function getCountTotal()
    {
        $f = $this->decorateCountCache(function () {
            return $this->query->getCountTotal();
        }, ICacher::TYPE_COUNT_TOTAL);

        return $f();
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
            $format = $this->format->setData($this->getResponse());

            if ($this->isCachable()) {
                $format = $this->prepareFormatCache()->setFormatter($format);
            }

            $this->format = $format;
            $this->is_format_ready = true;
        }

        return $this->format;
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
            'cache' => $this->generateCacheKey($this->query->current()),
        );
    }
}
