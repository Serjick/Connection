<?php

namespace Imhonet\Connection\DataFormat\Arr\PDO;

use Imhonet\Connection\DataFormat\IArr;

class Rekey implements IArr
{
    /**
     * @var \PDOStatement
     */
    private $data;

    private $cache;

    private $key_name;
    private $value_name;

    private $grouping = true;

    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    public function formatData()
    {
        if ($this->data && $this->cache === null) {

            $result = [];

            try {
                foreach ($this->data as $row) {

                    $cutted = null;

                    if (!$this->value_name) {
                        $cutted = $row;
                        unset($cutted[$this->key_name]);
                    }

                    if ($this->key_name) {
                        if ($this->grouping) {
                            $result[$row[$this->key_name]][] =
                                $this->value_name ? $row[$this->value_name] : $cutted;
                        } else {
                            $result[$row[$this->key_name]] =
                                $this->value_name ? $row[$this->value_name] : $cutted;
                        }

                    } else {
                        $result[] = $this->value_name ? $row[$this->value_name] : $cutted;
                    }

                }
            } catch (\Exception $e) {}

            $this->cache = $result;

        }

        return $this->cache ? : array();
    }

    public function setNewKey($key_name)
    {
        $this->key_name = $key_name;

        return $this;
    }

    public function setValueKey($value_name)
    {
        $this->value_name = $value_name;

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

}
