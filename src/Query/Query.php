<?php

namespace Imhonet\Connection\Query;

use Imhonet\Connection\Resource\IResource;

abstract class Query implements IQuery
{
    /**
     * @var \Exception|null
     */
    protected $error;

    /**
     * @var IResource
     */
    protected $resource;

    /**
     * @param IResource $resource
     * @return $this
     */
    public function setResource(IResource $resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * @return mixed
     * @throws \Exception
     */
    protected function getResource()
    {
        try {
            $resource = $this->resource->getHandle();
        } catch (\Exception $e) {
            $this->error = $e;
            throw $e;
        }

        return $resource;
    }

    /**
     * @inheritDoc
     */
    public function seek($position)
    {
        if ($position > 0) {
            throw new \OutOfBoundsException();
        }
    }

    /**
     * @inheritDoc
     */
    public function current()
    {
        // TODO: Implement current() method.
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        // TODO: Implement key() method.
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        // TODO: Implement next() method.
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        // TODO: Implement rewind() method.
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        // TODO: Implement valid() method.
    }

    /**
     * @inheritdoc
     */
    public function getDebugInfo($type = self::INFO_TYPE_QUERY)
    {
        $result = '';

        switch ($type) {
            case self::INFO_TYPE_ERROR:
                $result = $this->error ? $this->error->getMessage() : $result;
                break;
            case self::INFO_TYPE_BLOCKING:
                $result = self::BLOCKING_WAIT;
                break;
        }

        return $result;
    }
}
