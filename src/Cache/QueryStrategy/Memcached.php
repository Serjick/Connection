<?php

namespace Imhonet\Connection\Cache\QueryStrategy;

use Imhonet\Connection\Resource\IResource;
use Imhonet\Connection\Query\Memcached as Query;

class Memcached implements IQueryStrategy
{
    private $resource;

    public function __construct(IResource $resource)
    {
        $this->resource = $resource;
    }

    public function createGetQuery()
    {
        return (new Query\Get())->setResource($this->resource);
    }

    public function createSetQuery()
    {
        return (new Query\Set())->setResource($this->resource);
    }
}
