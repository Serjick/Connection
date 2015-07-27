<?php

namespace Imhonet\Connection\DataFormat\Iter\PDO;

use Imhonet\Connection\DataFormat\IIter;

class Get implements IIter
{
    /**
     * @var \PDOStatement|bool
     */
    private $data;

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function formatData()
    {
        return $this->data ? : new \EmptyIterator();
    }

    public function formatValue()
    {
    }
}
