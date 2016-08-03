<?php

namespace Imhonet\Connection\Query;

interface ICacheSetQuery extends IQuery
{
    /**
     * @param int $expire
     */
    public function setExpire($expire);

    /**
     * @param array
     */
    public function setData(array $data);
}
