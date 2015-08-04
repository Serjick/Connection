<?php

namespace Imhonet\Connection\DataFormat;

interface IMulti extends IDataFormat
{
    /**
     * @return int|null
     */
    public function getIndex();

    /**
     * @return bool
     */
    public function moveNext();
}
