<?php

namespace Imhonet\Connection\Query;

/**
 * @implements \SeekableIterator
 */
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
     * @var int[] stack of indexes for nested iterations
     */
    protected $iterations = array();

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
    private function getResponses(callable $filter = null)
    {
        $result = array();

        foreach ($this->getParent()->childs as $child) {
            if (!$child->disable(null) && ($filter === null || $filter($child))) {
                $result[] = $child->getResponse();
            }
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @see \SeekableIterator::seek
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
     * @inheritDoc
     * @see IQuery::current
     */
    public function current()
    {
        try {
            $result = $this->getQueryCurrent();
        } catch (\OutOfBoundsException $e) {
            $result = $this->getParent();
        }

        return $result;
    }

    /**
     * @inheritDoc
     * @see \SeekableIterator::key
     */
    public function key()
    {
        return $this->getParent()->query_id;
    }

    /**
     * @inheritDoc
     * @see \SeekableIterator::next
     */
    public function next()
    {
        ++$this->getParent()->query_id;
    }

    /**
     * @inheritDoc
     * @see \SeekableIterator::rewind
     */
    public function rewind()
    {
        $this->getParent()->iterations[] = $this->key();
        $this->seek(0);
    }

    /**
     * @inheritDoc
     * @see \SeekableIterator::valid
     */
    public function valid()
    {
        try {
            $result = (bool) $this->getQueryCurrent();
        } catch (\OutOfBoundsException $e) {
            $result = false;

            // restore previous iteration state if exists
            if (isset($this->getParent()->iterations[0])) {
                $this->seek(array_pop($this->getParent()->iterations));
            }
        }

        return $result;
    }

    /**
     * @throw \OutOfBoundsException
     */
    private function getQueryCurrent()
    {
        $query_id = $this->key();
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

    public function getCacheExpire()
    {
        try {
            $result = $this->getQueryCurrent()->getCacheExpireCurrent();
        } catch (\OutOfBoundsException $e) {
            $result = null;
        }

        return $result;
    }

    public function getCacheTags()
    {
        try {
            $result = $this->getQueryCurrent()->getCacheTagsCurrent();
        } catch (\OutOfBoundsException $e) {
            $result = [];
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
    /** @see IQuery::getCacheExpire */
    abstract protected function getCacheExpireCurrent();
    /** @see IQuery::getCacheTags */
    abstract protected function getCacheTagsCurrent();
}
