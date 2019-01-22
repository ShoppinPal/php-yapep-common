<?php

namespace ShoppinPal\YapepCommon\Storage;

class StorageFactory extends \YapepBase\Storage\StorageFactory
{
    const TYPE_SASL_MEMCACHED = 'saslMemcached';
    const TYPE_PREDIS         = 'predis';

    protected static function getStorage($configName, $storageType)
    {
        switch ($storageType) {
            case self::TYPE_SASL_MEMCACHED:
                return new SaslMemcachedStorage($configName);

            case self::TYPE_PREDIS:
                return new PredisStorage($configName);

            default:
                return parent::getStorage($configName, $storageType);
        }
    }

}
