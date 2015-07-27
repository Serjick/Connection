<?php

namespace Imhonet\Connection\DataFormat;

interface IDecorator
{
    /**
     * @param IDataFormat $formatter
     * @return self
     */
    public function setFormatter(IDataFormat $formatter);
}
