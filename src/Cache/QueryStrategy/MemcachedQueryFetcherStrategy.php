<?php

namespace Imhonet\Connection\Cache\QueryStrategy;

use Imhonet\Connection\Resource\IResource;
use Imhonet\Connection\Query\Memcached;

class MemcachedQueryFetcherStrategy implements IQueryFetcherStrategy
{
    private $resource;

    public function __construct(IResource $resource)
    {
        $this->resource = $resource;
    }

    public function createGetQuery()
    {
        return (new Memcached\Get())->setResource($this->resource);
    }

    public function createSetQuery()
    {
        return (new Memcached\Set())->setResource($this->resource);
    }
}
