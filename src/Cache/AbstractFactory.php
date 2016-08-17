<?php

namespace Imhonet\Connection\Cache;

use Imhonet\Connection\Request;

abstract class AbstractFactory
{
    /** @type Request */
    private $request;

    private $expire;
    private $tags;

    /**
     * @param Request $request
     * @return self
     */
    public static function getInstance(Request $request)
    {
        return (new static)
            ->setRequest($request)
        ;
    }

    /**
     * @param Request $request
     * @return self
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;

        return $this;
    }

    /**
     * @param int $expire
     * @return self
     */
    public function setExpire($expire)
    {
        $this->expire = $expire;

        return $this;
    }

    /**
     * @param string[] $tags
     * @return self
     */
    public function setCommonTags(array $tags)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return Request
     */
    public function getRequestCached()
    {
        $cacher = $this->getCacher();

        if ($this->expire !== null) {
            $cacher->setExpire($this->expire);
        }

        if ($this->tags !== null) {
            $cacher->setTags($this->tags);
        }

        return $this->request->setCacher($cacher, $this->getDataFormat());
    }

    /**
     * @return Cacher
     * @see CacherFactory
     */
    abstract protected function getCacher();

    protected function getDataFormat()
    {
        return new DataFormat();
    }
}
