<?php

namespace Imhonet\Connection\DataFormat\Arr\Sphinx;

use Imhonet\Connection\DataFormat\IArr;

class GetIds implements IArr
{
    /**
     * @var array|bool
     */
    private $data;

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function formatData()
    {
        return ($this->data === false || !isset($this->data[0]['matches'])) ? array() : array_keys($this->data[0]['matches']);
    }

    public function formatValue()
    {
    }
}
