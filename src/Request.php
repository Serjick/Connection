<?php

namespace Imhonet\Connection;

use Imhonet\Connection\DataFormat\IDataFormat;
use Imhonet\Connection\DataFormat\IMulti;
use Imhonet\Connection\Query\IQuery;
use Imhonet\Connection\Resource\IResource;

class Request implements \Iterator
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

    private $is_valid_iteration = true;

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
        $result = $this->getFormater()->formatData();
        $this->query->seek($this->key());

        return $result;
    }

    /**
     * @return float|int|null|string
     */
    public function getValue()
    {
        $result = $this->getFormater()->formatValue();
        $this->query->seek($this->key());

        return $result;
    }

    /**
     * @return int
     */
    public function getErrorCode()
    {
        return $this->query->getErrorCode();
    }

    /**
     * @return int
     */
    public function getCount()
    {
        return $this->query->getCount();
    }

    /**
     * @return int|null
     */
    public function getCountTotal()
    {
        return $this->query->getCountTotal();
    }

    /**
     * @return int|null
     */
    public function getLastId()
    {
        return $this->query->getLastId();
    }

    /**
     * @return IDataFormat|IMulti
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
}
