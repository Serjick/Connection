<?php

namespace Imhonet\Connection\Cache;

interface ICachable
{
    /**
     * @return string
     */
    public function getCacheKey();
}
