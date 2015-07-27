<?php

namespace Imhonet\Connection\DataFormat\Arr\Memcached;

use Imhonet\Connection\DataFormat\IArr;

class Get implements IArr
{
    /**
     * @var array
     */
    protected $data;

    /**
     * @inheritdoc
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function formatData()
    {
        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function formatValue()
    {
    }
}
