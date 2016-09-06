<?php

namespace Imhonet\Connection\DataFormat\Arr\PDO;

use Imhonet\Connection\DataFormat\IArr;
use Imhonet\Connection\Cache\ICachable;

class Group implements IArr, ICachable
{
    /**
     * @var \PDOStatement|bool
     */
    private $data;

    private $groups = [];

    public function addGroup($group_field)
    {
        $this->groups[] = $group_field;

        return $this;
    }

    /**
     * @param mixed $data
     * @return $this
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @return array
     */
    public function formatData()
    {
        $result = [];

        if ($this->data) {
            try {
                foreach ($this->data as $row) {
                    $cutted = $row;
                    $point = & $result;
                    foreach ($this->groups as $group) {
                        assert(isset($cutted[$group]));

                        if (!isset($point[$cutted[$group]])) {
                            $point[$cutted[$group]] = [];
                        }
                        $point = & $point[$cutted[$group]];
                        unset($cutted[$group]);
                    }
                    $point[] = $cutted;
                }
            } catch (\Exception $e) {
            }
        }

        return $result;
    }

    /**
     * @return null
     */
    public function formatValue()
    {
        return null;
    }

    public function getCacheKey()
    {
        return get_class($this) . '_' . implode('_', $this->groups);
    }
}
