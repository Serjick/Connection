<?php

namespace Imhonet\Connection\DataFormat\Scalar\PDO;

use Imhonet\Connection\DataFormat\IScalar;

class Get implements IScalar
{
    /**
     * @var \PDOStatement
     */
    private $data;
    private $result;
    private $has_result = false;

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function formatData()
    {
        return array();
    }

    public function formatValue()
    {
        if (!$this->has_result) {
            $this->result = $this->data ? $this->data->fetchColumn() : null;
            $this->has_result = true;
        }

        return $this->result;
    }
}
