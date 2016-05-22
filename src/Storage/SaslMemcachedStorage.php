<?php

namespace ShoppinPal\YapepCommon\Storage;

use Memcached;
use YapepBase\Storage\MemcachedStorage;

class SaslMemcachedStorage extends MemcachedStorage
{

    public function __construct($configName)
    {
        parent::__construct($configName);
        $this->memcache->setOption(Memcached::OPT_BINARY_PROTOCOL, true);
        $this->memcache->setOption(Memcached::OPT_NO_BLOCK, true);
        $this->memcache->setOption(Memcached::OPT_AUTO_EJECT_HOSTS, true);
        $this->memcache->setOption(Memcached::OPT_CONNECT_TIMEOUT, 2000);
        $this->memcache->setOption(Memcached::OPT_POLL_TIMEOUT, 2000);
        $this->memcache->setOption(Memcached::OPT_RETRY_TIMEOUT, 2);

        $this->memcache->setSaslAuthData(getenv('MEMCACHIER_USERNAME'), getenv('MEMCACHIER_PASSWORD'));
    }
}
