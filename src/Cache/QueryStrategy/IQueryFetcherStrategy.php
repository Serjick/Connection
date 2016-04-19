<?php

namespace Imhonet\Connection\Cache\QueryStrategy;

interface IQueryFetcherStrategy
{
    /**
     * @return IQuery
     */
    public function createGetQuery();

    /**
     * @return IQuery
     */
    public function createSetQuery();
}
