<?php

namespace Imhonet\Connection\Cache\Query;

use Imhonet\Connection\Query\IQuery;

interface ISet extends IQuery
{
    /**
     * @param int $expire
     * @return $this|self
     */
    public function setExpire($expire);

    /**
     * @param array<string, mixed> $data [key => value, ...]
     * @return $this|self
     */
    public function setData(array $data);
}
