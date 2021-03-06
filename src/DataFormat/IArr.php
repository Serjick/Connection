<?php

namespace Imhonet\Connection\DataFormat;

interface IArr extends IDataFormat
{
    /**
     * @param mixed $data
     * @return $this
     */
    public function setData($data);

    /**
     * @return array
     */
    public function formatData();

    /**
     * @return null
     */
    public function formatValue();
}
