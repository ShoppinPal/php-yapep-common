<?php

namespace ShoppinPal\YapepCommon\Queue;

use ShoppinPal\YapepCommon\Storage\StorageFactory;
use YapepBase\Config;

class KestrelQueue implements IQueue
{
    protected $storage;

    protected $autoClosePreviousReliableRead = false;

    protected $reliableReads = true;

    public function __construct($configName)
    {
        $config                              = Config::getInstance();
        $storageName                         = $config->get('commonResource.kestrel.' . $configName . '.storageName');
        $this->storage                       = StorageFactory::get($storageName);
        $this->reliableReads                 = $config->get(
            'commonResource.kestrel.' . $configName . '.reliableReads',
            true
        );
        $this->autoClosePreviousReliableRead = $config->get(
            'commonResource.kestrel.' . $configName . '.autoClosePreviousReliableRead',
            false
        );
    }

    public function sendMessage($queueConfigName, $messageBody)
    {
        $this->storage->set($queueConfigName, json_encode($messageBody));
    }

    public function receiveMessage($queueConfigName, $waitTimeSeconds = 0)
    {
        $getParts = [$queueConfigName];

        if ($waitTimeSeconds > 0) {
            $getParts[] = 't=' . (int)$waitTimeSeconds * 1000;
        }

        if ($this->reliableReads) {
            if ($this->autoClosePreviousReliableRead) {
                $getParts[] = 'close';
            }
            $getParts[] = 'open';
        }

        $result = $this->storage->get(implode('/', $getParts));

        return empty($result) ? null : new QueueMessage(json_decode($result, true));
    }

    public function deleteMessage($queueConfigName, $deleteId)
    {
        $this->storage->get($queueConfigName . '/close');
    }
}
