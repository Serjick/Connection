<?php

namespace Imhonet\Connection\Cache;

use Imhonet\Connection\Query\Memcached;
use Imhonet\Connection\Query;
use Imhonet\Connection\Cache\QueryStrategy;
use Imhonet\Connection\Resource;

class CacherFactory
{
    public static function getInstanceMemcached($host, $port, $tags_host = null, $tags_port = null)
    {
        $resource = Resource\Factory::getInstance()
            ->setHost($host)
            ->setPort($port)
            ->getResource(Resource\Factory::TYPE_MEMCACHED);
        $tags_resource = null;

        if ($tags_host !== null && $tags_port !== null) {
            $tags_resource = Resource\Factory::getInstance()
                ->setHost($tags_host)
                ->setPort($tags_port)
                ->getResource(Resource\Factory::TYPE_MEMCACHED);
        }

        return self::getInstanceMemcachedResource($resource, $tags_resource);
    }

    public static function getInstanceMemcachedResource(Resource\IResource $resource, Resource\IResource $tags_resource = null)
    {
        $main_query_fetcher = new QueryStrategy\Memcached($resource);
        $tag_query_fetcher = null;

        if ($tags_resource !== null) {
            $tag_query_fetcher = new QueryStrategy\Memcached($tags_resource);
        }

        return new Cacher($main_query_fetcher, $tag_query_fetcher);
    }
}
