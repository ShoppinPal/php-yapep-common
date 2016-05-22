<?php

namespace ShoppinPal\YapepCommon\Storage;

class StorageFactory extends \YapepBase\Storage\StorageFactory
{

    protected static function getStorage($configName, $storageType)
    {
        if ($storageType == 'saslMemcached') {
            return new SaslMemcachedStorage($configName);
        }
        return parent::getStorage($configName, $storageType);
    }

}
