<?php

namespace Imhonet\Connection\Query;

trait TImmutable
{
    private $query_id = 0;
    /**
     * @type IQuery|self
     */
    private $parent;
    /**
     * @type IQuery[]|self[]
     */
    private $childs = array();

    /**
     * @return self|$this
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

    /**
     * @return self|$this
     */
    private function &getParent()
    {
        if (!$this->parent) {
            $this->parent = $this;
        }

        return $this->parent;
    }

    /**
     * @param callable $filter e.g. function(self $child): bool {}
     * @return array
     */
    private function getResponses(Callable $filter = null)
    {
        $result = array();

        foreach ($this->getParent()->childs as $child) {
            if ($filter === null || $filter($child)) {
                $result[] = $child->getResponse();
            }
        }

        return $result;
    }

    /**
     * Seeks to a position
     * @link http://php.net/manual/en/seekableiterator.seek.php
     * @param int $position <p>
     * The position to seek to.
     * </p>
     * @return void
     * @throw \OutOfBoundsException
     * @since 5.1.0
     */
    public function seek($position)
    {
        if (isset($this->getParent()->childs[$position])) {
            $this->getParent()->query_id = $position;
        } else {
            throw new \OutOfBoundsException();
        }
    }

    /**
     * @throw \OutOfBoundsException
     */
    private function getQueryCurrent()
    {
        $query_id = $this->getParent()->query_id;
        $this->seek($query_id);

        return $this->getParent()->childs[$query_id];
    }

    public function getErrorCode()
    {
        try {
            $result = $this->getQueryCurrent()->getErrorCodeCurrent();
        } catch (\OutOfBoundsException $e) {
            $result = null;
        }

        return $result;
    }

    public function getCount()
    {
        try {
            $result = $this->getQueryCurrent()->getCountCurrent();
        } catch (\OutOfBoundsException $e) {
            $result = null;
        }

        return $result;
    }

    public function getCountTotal()
    {
        try {
            $result = $this->getQueryCurrent()->getCountTotalCurrent();
        } catch (\OutOfBoundsException $e) {
            $result = null;
        }

        return $result;
    }

    public function getLastId()
    {
        try {
            $result = $this->getQueryCurrent()->getLastIdCurrent();
        } catch (\OutOfBoundsException $e) {
            $result = null;
        }

        return $result;
    }

    public function getDebugInfo($type = IQuery::INFO_TYPE_QUERY)
    {
        try {
            $result = $this->getQueryCurrent()->getDebugInfoCurrent($type);
        } catch (\OutOfBoundsException $e) {
            $result = null;
        }

        return $result;
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
    /** @see IQuery::getDebugInfo */
    abstract protected function getDebugInfoCurrent($type = IQuery::INFO_TYPE_QUERY);
}
