<?php

namespace Imhonet\Connection\Query;

/**
 * @todo iterable parent
 */
trait TImmutable
{
    private $query_id = 0;
    /**
     * @type Query|self
     */
    private $parent;
    /**
     * @type Query[]|self[]
     */
    private $childs = array();

    /**
     * @return $this
     */
    private function addChild()
    {
        $parent = $this->getParent();
        $child = clone $parent;
        $query_id = array_push($parent->childs, $child) - 1;
        $child->query_id = $query_id;
        $child->childs = null;
        $child->parent = $parent;

        return $child;
    }

    private function &getParent()
    {
        if (!$this->parent) {
            $this->parent = $this;
        }

        return $this->parent;
    }

    /**
     * @return array
     */
    private function getResponses()
    {
        $result = array();

        foreach ($this->getParent()->childs as $child) {
            $result[] = $child->getResponse();
        }

        return $result;
    }

    public function getErrorCode()
    {
        $query = $this->parent->childs[$this->query_id];

        return $query ? $query->getErrorCodeCurrent() : null;
    }

    public function getCount()
    {
        $query = $this->parent->childs[$this->query_id];

        return $query ? $query->getCountCurrent() : null;
    }

    public function getCountTotal()
    {
        $query = $this->parent->childs[$this->query_id];

        return $query ? $query->getCountTotalCurrent() : null;
    }

    public function getLastId()
    {
        $query = $this->parent->childs[$this->query_id];

        return $query ? $query->getLastIdCurrent() : null;
    }

    abstract protected function getResponse();
    /** @see IQuery::getErrorCode */
    abstract protected function getErrorCodeCurrent();
    /** @see IQuery::getCount */
    abstract protected function getCountCurrent();
    /** @see IQuery::getCountTotal */
    abstract protected function getCountTotalCurrent();
    /** @see IQuery::getLastId */
    abstract protected function getLastIdCurrent();
}
