<?php

namespace Imhonet\Connection;

interface IErrorable
{
    /**
     * @return int bitmask of Imhonet\Connection\Query\IQuery::STATUS_*
     */
    public function getErrorCode();
}
