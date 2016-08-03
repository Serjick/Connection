<?php

namespace Imhonet\Connection\Cache\QueryStrategy;

interface IQueryFetcherStrategy
{
    /**
     * @return ICacheGetQuery
     */
    public function createGetQuery();

    /**
     * @return ICacheSetQuery
     */
    public function createSetQuery();
}
