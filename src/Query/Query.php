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

    protected $next = true;
    
    protected $disable = false;

    private $expire;
    private $tags = [];

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
    public function __clone()
    {
        if ($this->resource) {
            $this->resource = clone $this->resource;
        }
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
        return $this;
    }

    /**
     * @inheritDoc
     */
    public function key()
    {
        return 0;
    }

    /**
     * @inheritDoc
     */
    public function next()
    {
        $this->next = false;
    }

    /**
     * @inheritDoc
     */
    public function rewind()
    {
        $this->next = true;
    }

    /**
     * @inheritDoc
     */
    public function valid()
    {
        return $this->next;
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

    /**
     * @inheritDoc
     */
    public function getCacheKey()
    {
        return '';
    }

    /**
     * @inheritDoc
     */
    public function disable($state = true)
    {
        if (is_bool($state)) {
            $this->disable = $state;
        }

        return $this->disable;
    }

    /**
     * @inheritDoc
     */
    public function setCacheExpire($expire)
    {
        $this->expire = $expire;

        return $this;
    }

    final protected function getCacheExpireCurrent()
    {
        return $this->expire;
    }

    /**
     * @inheritDoc
     */
    public function getCacheExpire()
    {
        return $this->getCacheExpireCurrent();
    }

    /**
     * @inheritDoc
     */
    public function setCacheTags(array $tags)
    {
        $this->tags = array_merge($this->tags, $tags);

        return $this;
    }

    final protected function getCacheTagsCurrent()
    {
        return $this->tags;
    }

    /**
     * @inheritDoc
     */
    public function getCacheTags()
    {
        return $this->getCacheTagsCurrent();
    }
}
