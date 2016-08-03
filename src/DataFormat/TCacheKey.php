<?php

namespace Imhonet\Connection\DataFormat;

trait TCacheKey
{

    /**
     * @inheritDoc
     */
    public function getCacheKey()
    {
        return get_class($this);
    }
}
