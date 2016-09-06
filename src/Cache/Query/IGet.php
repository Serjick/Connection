<?php

namespace Imhonet\Connection\Cache\Query;

use Imhonet\Connection\Query\IQuery;

interface IGet extends IQuery
{
    /**
     * @param array
     */
    public function setKeys(array $keys);
}
