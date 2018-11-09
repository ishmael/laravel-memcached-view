<?php

/**
 *  MemcacheStorage A filesystem with memcached base
 *
 *  We use cache.stores.memcached.servers for server configuration.
 *  Please make sure the configuration of memcached in config/cache.php is correct first.
 *
 */

namespace PIXNET\MemcachedView\Filesystem;

use Illuminate\Support\Facades\Cache;

class MemcacheStorage extends \Illuminate\Filesystem\Filesystem
{
    public function exists($key)
    {
        return Cache::has($key);
    }

    public function get($key, $lock = false)
    {
        $value = Cache::get($key);

        return $value ? $value['content'] : null;
    }

    public function put($key, $value, $lock = false)
    {
        return Cache::add($key, ['content' => $value, 'modified' => time()], 5);
    }

    public function lastModified($key)
    {
        $value = Cache::get($key);

        return $value ? $value['modified'] : null;
    }
}
