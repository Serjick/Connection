<?php

namespace Imhonet\Connection\DataFormat\Arr;

use Imhonet\Connection\DataFormat\IArr;
use Imhonet\Connection\DataFormat\IDecorator;
use Imhonet\Connection\DataFormat\TDecorator;

class Rekey implements IArr, IDecorator
{
    use TDecorator;

    /**
     * @var array|null
     */
    private $data;
    private $is_processed = false;

    private $index_key;
    private $value_key;
    private $grouping = false;

    /**
     * @param array|null $data
     * @inheritdoc
     */
    public function setData($data)
    {
        assert(is_array($data) || $data === null);
        $this->data = $data;

        return $this;
    }

    public function formatData()
    {
        if (!$this->is_processed) {
            assert($this->index_key !== null);
            $result = array();
            $data = $this->getData();

            if (!is_array($data) || $this->index_key === null) {
            } elseif (!$this->grouping) {
                $result = array_column($data, $this->value_key, $this->index_key);
            } else {
                foreach ($data as $row) {
                    $key_value = $row[$this->index_key];

                    if (!isset($result[$key_value])) {
                        $result[$key_value] = array();
                    }

                    $result[$key_value][] = $this->value_key === null ? $row : $row[$this->value_key];
                }
            }

            $this->data = $result;
            $this->is_processed = true;
        }

        return $this->data;
    }

    public function setIndexKey($name)
    {
        $this->index_key = $name;

        return $this;
    }

    public function setValueKey($name)
    {
        $this->value_key = $name;

        return $this;
    }

    public function formatValue()
    {
    }

    public function setGrouping($grouping = true)
    {
        $this->grouping = $grouping;

        return $this;
    }

    protected function getDataRaw()
    {
        return $this->data;
    }
}
