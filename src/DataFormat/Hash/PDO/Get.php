<?php

namespace Imhonet\Connection\DataFormat\Hash\PDO;

use Imhonet\Connection\DataFormat\IHash;

class Get implements IHash
{
    /**
     * @var \PDOStatement
     */
    private $data;
    private $result;

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function formatData()
    {
        if ($this->result === null) {
            if (!$this->data || !$this->result = $this->data->fetch(\PDO::FETCH_ASSOC)) {
                $this->result = array();
            }
        }

        return $this->result;
    }

    public function formatValue()
    {
    }
}
