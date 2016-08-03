<?php

namespace Imhonet\Connection\Cache;

use Imhonet\Connection\Resource\IResource;
use Imhonet\Connection\Query\Memcached;
use Imhonet\Connection\Query;
use Imhonet\Connection\Cache\QueryStrategy;

class CacherFactory
{
    public static function instanceCacherMemcache($host, $port, $tags_host = null, $tags_port = null)
    {
        $resource = \Imhonet\Connection\Resource\Factory::getInstance()
            ->setHost($host)
            ->setPort($port)
            ->getResource(\Imhonet\Connection\Resource\Factory::TYPE_MEMCACHED);
        
        $main_query_fetcher = new QueryStrategy\MemcachedQueryFetcherStrategy($resource);

        $tag_query_fetcher = null;

        if ($tags_host !== null && $tags_port !== null) {
            $tags_resource = \Imhonet\Connection\Resource\Factory::getInstance()
                ->setHost($tags_host)
                ->setPort($tags_port)
                ->getResource(\Imhonet\Connection\Resource\Factory::TYPE_MEMCACHED);

            $tag_query_fetcher = new QueryStrategy\MemcachedQueryFetcherStrategy($tags_resource);
        }

        return new Cacher($main_query_fetcher, $tag_query_fetcher);
    }
}
