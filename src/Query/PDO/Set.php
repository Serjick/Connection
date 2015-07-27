<?php

namespace Imhonet\Connection\Query\PDO;

class Set extends PDO
{
    private $last_id;

    protected function getResponse()
    {
        $response = parent::getResponse();
        $this->getResourceLastInsertId();

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function getCountTotal()
    {
        return $this->getCount();
    }

    /**
     * @inheritdoc
     */
    public function getCount()
    {
        return $this->getResponse()->rowCount();
    }

    private function getResourceLastInsertId()
    {
        if ($this->last_id === null) {
            try {
                $resource = $this->getResource();
            } catch (\Exception $e) {
                $this->error = $e;
            }

            if (isset($resource)) {
                $this->last_id = (int) $resource->lastInsertId();
            }
        }

        return $this->last_id;
    }

    /**
     * @inheritdoc
     */
    public function getLastId()
    {
        $this->execute();

        return $this->getResourceLastInsertId();
    }
}
