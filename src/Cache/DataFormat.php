<?php

namespace Imhonet\Connection\Cache;

use Imhonet\Connection\DataFormat\IDataFormat;
use Imhonet\Connection\DataFormat\IDecorator;
use Imhonet\Connection\DataFormat\IMulti;
use Imhonet\Connection\IErrorable;
use Imhonet\Connection\Query\IQuery;

class DataFormat implements IDataFormat, IDecorator, IMulti, IErrorable, ICachable
{
    private $data;

    /**
     * @type IDataFormat|IMulti|IErrorable|ICachable
     */
    private $formatter;
    private $use_formatter = false;

    /**
     * @inheritdoc
     */
    public function setFormatter(IDataFormat $formatter)
    {
        assert($formatter instanceof ICachable);
        $this->formatter = $formatter;

        return $this;
    }

    /**
     * @inheritdoc
     * @param array<int, mixed|CacheException> $data [query_id => data_or_exception, ...]
     */
    public function setData($data)
    {
        $this->data = $data;
        $this->use_formatter = !$data;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function formatData()
    {
        if ($this->use_formatter) {
            $result = $this->formatter->formatData();
        } else {
            $key = $this->getIndex();
            $result = is_array($this->data[$key]) || $this->data[$key] instanceof \Traversable ? $this->data[$key] : [];
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function formatValue()
    {
        if ($this->use_formatter) {
            $result = $this->formatter->formatValue();
        } else {
            $key = $this->getIndex();
            $result = is_scalar($this->data[$key]) ? $this->data[$key] : null;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getErrorCode()
    {
        if ($this->use_formatter) {
            $result = $this->formatter instanceof IErrorable ? $this->formatter->getErrorCode() : 0;
        } else {
            $key = $this->getIndex();
            $result = $this->data[$key] instanceof CacheException
                ? IQuery::STATUS_ERROR | IQuery::STATUS_TEMPORARY_UNAVAILABLE
                : 0;
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getIndex()
    {
        return $this->use_formatter ? $this->getIndexFormatter() : $this->getIndexCached();
    }

    private function getIndexCached()
    {
        return key($this->data);
    }

    private function getIndexFormatter()
    {
        return $this->formatter instanceof IMulti ? $this->formatter->getIndex() : 0;
    }

    /**
     * @inheritdoc
     */
    public function moveNext()
    {
        $result = false;

        if ($this->formatter instanceof IMulti) {
            if ($this->use_formatter) {
                $result = $this->formatter->moveNext();
            } else {
                next($this->data);
                $result = $this->getIndexCached() !== null;

                if (!$result) {
                    $result = $this->getIndexFormatter() !== null;
                    $this->use_formatter = true;
                }
            }
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function getCacheKey()
    {
        return $this->formatter->getCacheKey();
    }
}
