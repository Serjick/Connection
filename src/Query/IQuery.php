<?php

namespace Imhonet\Connection\Query;

use Imhonet\Connection\IErrorable;
use Imhonet\Connection\Cache\ICachable;
use Imhonet\Connection\Resource\IResource;

interface IQuery extends \SeekableIterator, IErrorable, ICachable
{
    const STATUS_OK = 0;
    const STATUS_ERROR = 1;
    const STATUS_WARNING = 2;
    const STATUS_NOTICE = 4;
    const STATUS_INFO = 8;
    const STATUS_TEMPORARY_UNAVAILABLE = 1024;
    const STATUS_STALE_DATA = 2048;
    const STATUS_INCONSISTENT_PARAMETERS = 4096;
    const STATUS_INSUFFICIENT_DATA = 8192;
    const STATUS_INCONSISTENT_DATA = 16384;

    const INFO_TYPE_QUERY = 1;
    const INFO_TYPE_ERROR = 2;
    const INFO_TYPE_BLOCKING = 3;
    /** query duration in seconds */
    const INFO_TYPE_DURATION = 4;
    const INFO_TYPE_PROFILING_KEY = 5;

    const BLOCKING_WAIT = 'blocking';
    const BLOCKING_FREE = 'non-blocking';

    /**
     * @param IResource $resource
     * @return self
     */
    public function setResource(IResource $resource);

    /**
     * @return mixed
     */
    public function execute();

    /**
     * @return int
     */
    public function getCountTotal();

    /**
     * @return int
     */
    public function getCount();

    /**
     * @return int|null
     */
    public function getLastId();

    /**
     * @param int $type self::INFO_TYPE_*
     * @return string
     */
    public function getDebugInfo($type = self::INFO_TYPE_QUERY);

    /**
     * @param int $expire
     * @return self
     */
    public function setCacheExpire($expire);

    /**
     * @return int|null
     */
    public function getCacheExpire();

    /**
     * @param string[] $tags
     * @return self
     */
    public function setCacheTags(array $tags);

    /**
     * @return string[]
     */
    public function getCacheTags();

    /**
     * @param bool|null $state
     * @return bool
     */
    public function disable($state = true);

    /**
     * @inheritDoc
     * @return self
     */
    public function current();
}
