<?php

namespace Imhonet\Connection\Cache\QueryStrategy;

interface IQueryStrategy
{
    /**
     * @return \Imhonet\Connection\Cache\Query\IGet
     */
    public function createGetQuery();

    /**
     * @return \Imhonet\Connection\Cache\Query\ISet
     */
    public function createSetQuery();
}
