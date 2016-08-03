<?php

namespace Imhonet\Connection\Query;

interface ICacheGetQuery extends IQuery
{
    /**
     * @param array
     */
    public function setKeys(array $keys);
}
