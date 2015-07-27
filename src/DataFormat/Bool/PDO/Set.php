<?php

namespace Imhonet\Connection\DataFormat\Bool\PDO;

use Imhonet\Connection\DataFormat\IBool;

class Set implements IBool
{
    /**
     * @var \PDOStatement|bool
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
        return array();
    }

    /**
     * @inheritdoc
     */
    public function formatValue()
    {
        return $this->data && $this->data->rowCount();
    }
}
