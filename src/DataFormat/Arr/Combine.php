<?php

namespace Imhonet\Connection\DataFormat\Arr;

use Imhonet\Connection\DataFormat\IArr;

class Combine implements IArr
{
    /**
     * @var array
     */
    protected $data;
    private $keys = array();
    private $is_combined = false;

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

    /**
     * @inheritdoc
     */
    public function formatData()
    {
        if (!$this->is_combined) {
            assert(!empty($this->keys));
            $this->is_combined = true;

            if (is_array($this->data) && $this->keys) {
                foreach ($this->data as & $row) {
                    $row = array_combine($this->keys, $row);
                }
                unset($row);
            } else {
                $this->data = array();
            }
        }

        return $this->data;
    }

    /**
     * @inheritdoc
     */
    public function formatValue()
    {
    }

    /**
     * @param string[] $keys
     * @return self
     */
    public function setKeys(array $keys)
    {
        $this->keys = $keys;

        return $this;
    }
}
