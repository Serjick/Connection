<?php
/**
 * Created by JetBrains PhpStorm.
 * User: Shiyanov
 * Date: 30.06.14
 * Time: 16:05
 * To change this template use File | Settings | File Templates.
 */

namespace Imhonet\Connection\DataFormat\Arr\Sphinx;

use Imhonet\Connection\DataFormat\IArr;

class Rekey implements IArr
{
    /**
     * @var array|bool
     */
    private $data;

    private $field_key;
    private $field_value;

    private $result;

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
        if ($this->result === null && $this->data !== false && isset($this->data[0]['matches'])) {
            $attrs = array_column($this->data[0]['matches'], 'attrs');
            $this->result = array_column($attrs, $this->field_value, $this->field_key);
        } else {
            $this->result = array();
        }

        return $this->result;
    }

    /**
     * @param string $field
     * @return self
     */
    public function setNewKey($field)
    {
        $this->field_key = $field;

        return $this;
    }

    /**
     * @param string $field
     * @return self
     */
    public function setValueKey($field)
    {
        $this->field_value = $field;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function formatValue()
    {
    }
}
