<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Storage;


use Predis\Client;
use YapepBase\Debugger\Item\StorageItem;
use YapepBase\Exception\StorageException;
use YapepBase\Exception\ConfigException;
use YapepBase\Application;
use YapepBase\Storage\IIncrementable;
use YapepBase\Storage\StorageAbstract;

/**
 * Storage backend, that uses Predis Package.
 *
 * Configuration options:
 *     <ul>
 *         <li>host:             The redis server's hostname or IP.</li>
 *         <li>port:             The port of the redis server. Optional, defaults to 11211</li>
 *         <li>keyPrefix:        The keys will be prefixed with this string. Optional, defaults to empty string.</li>
 *         <li>keySuffix:        The keys will be suffixed with this string. Optional, defaults to empty string.</li>
 *         <li>hashKey:          If TRUE, the key will be hashed before being stored. Optional, defaults to FALSE.</li>
 *         <li>readOnly:         If TRUE, the storage instance will be read only, and any write attempts will
 *                               throw an exception. Optional, defaults to FALSE</li>
 *         <li>debuggerDisabled: If TRUE, the storage will not add the requests to the debugger if it's available.
 *                               This is useful for example for a storage instance, that is used to store the
 *                               DebugDataCreator's debug information as they can become quite large, and if they were
 *                               sent to the client it can cause problems. Optional, defaults to FALSE.
 *     </ul>
 */
class PredisStorage extends StorageAbstract implements IIncrementable
{

    /** @var \Predis\Client */
    protected $predisClient;

    /** @var string */
    protected $host;

    /** @var int */
    protected $port;

    /** @var string */
    protected $keyPrefix;

    /** @var string */
    protected $keySuffix;

    /** @var bool */
    protected $hashKey;

    /** @var bool */
    protected $readOnly = false;

    /** @var bool */
    protected $debuggerDisabled;

    /**
     * @return array
     */
    protected function getConfigProperties()
    {
        return array(
            'host',
            'port',
            'keyPrefix',
            'keySuffix',
            'hashKey',
            'readOnly',
            'debuggerDisabled'
        );
    }


    protected function setupConfig(array $config)
    {
        if (empty($config['host'])) {
            throw new ConfigException('Host is not set for PredisStorage: ' . $this->currentConfigurationName);
        }
        $this->host             = $config['host'];
        $this->port             = empty($config['port']) ? 6379 : (int)$config['port'];
        $this->keyPrefix        = empty($config['keyPrefix']) ? '' : $config['keyPrefix'];
        $this->keySuffix        = empty($config['keySuffix']) ? '' : $config['keySuffix'];
        $this->hashKey          = empty($config['hashKey']) ? false : (bool)$config['hashKey'];
        $this->readOnly         = empty($config['readOnly']) ? false : (bool)$config['readOnly'];
        $this->debuggerDisabled = empty($config['debuggerDisabled']) ? false : (bool)$config['debuggerDisabled'];

        $connectionParams = [
            'host' => $this->host,
            'port' => $this->port
        ];
        $this->predisClient = new Client($connectionParams);
    }


    protected function makeKey(string $key)
    {
        $key = $this->keyPrefix . $key . $this->keySuffix;
        if ($this->hashKey) {
            $key = md5($key);
        }
        return $key;
    }

    /**
     * @inheritdoc
     */
    public function set($key, $data, $ttl = 0)
    {
        if ($this->readOnly) {
            throw new StorageException('Trying to write to a read only storage');
        }
        $debugger = Application::getInstance()->getDiContainer()->getDebugger();

        $startTime = microtime(true);

        $ttl = empty($ttl) ? null : $ttl;

        if (!$this->predisClient->set($this->makeKey($key), json_encode($data), 'ex', $ttl)) {
            throw new StorageException('Unable to store value in predis');
        }

        if (!$this->debuggerDisabled && $debugger !== false) {
            $executionTime = microtime(true) - $startTime;

            $debugger->addItem(
                new StorageItem(
                    'redis',
                    'redis.' . $this->currentConfigurationName,
                    StorageItem::METHOD_SET . ' ' . $key . ' for ' . $ttl,
                    $data,
                    $executionTime
                )
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function get($key)
    {
        $debugger = Application::getInstance()->getDiContainer()->getDebugger();

        $startTime = microtime(true);

        $result = $this->predisClient->get($this->makeKey($key));

        if (!$this->debuggerDisabled && $debugger !== false) {
            $executionTime = microtime(true) - $startTime;

            $debugger->addItem(
                new StorageItem(
                    'redis',
                    'redis.' . $this->currentConfigurationName,
                    StorageItem::METHOD_GET . ' ' . $key,
                    $result, $executionTime
                )
            );
        }

        return empty($result)
            ? false
            : json_decode($result, true);
    }

    /**
     * @inheritdoc
     */
    public function delete($key)
    {
        if ($this->readOnly) {
            throw new StorageException('Trying to write to a read only storage');
        }
        $debugger = Application::getInstance()->getDiContainer()->getDebugger();

        $startTime = microtime(true);

        $this->predisClient->del([$this->makeKey($key)]);

        // If we have a debugger, we have to log the request
        if (!$this->debuggerDisabled && $debugger !== false) {
            $debugger->addItem(
                new StorageItem(
                    'redis',
                    'redis.' . $this->currentConfigurationName,
                    StorageItem::METHOD_DELETE . ' ' . $key,
                    null,
                    microtime(true) - $startTime
                )
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function clear()
    {
        if ($this->readOnly) {
            throw new StorageException('Trying to write to a read only storage');
        }
        $debugger = Application::getInstance()->getDiContainer()->getDebugger();

        $startTime = microtime(true);

        $this->predisClient->flushall();

        // If we have a debugger, we have to log the request
        if (!$this->debuggerDisabled && $debugger !== false) {
            $debugger->addItem(
                new StorageItem(
                    'redis',
                    'redis.' . $this->currentConfigurationName,
                    StorageItem::METHOD_CLEAR,
                    null,
                    microtime(true) - $startTime
                )
            );
        }
    }

    /**
     * @inheritdoc
     */
    public function increment($key, $offset, $ttl = 0)
    {
        return $this->predisClient->incrby($this->makeKey($key), $offset);
    }

    /**
     * @inheritdoc
     */
    public function isPersistent()
    {
        return false;
    }

    /**
     * @inheritdoc
     */
    public function isTtlSupported()
    {
        return true;
    }

    /**
     * @inheritdoc
     */
    public function isReadOnly()
    {
        return $this->readOnly;
    }

    /**
     * @inheritdoc
     */
    public function getConfigData()
    {
        return array(
            'storageType'      => StorageFactory::TYPE_PREDIS,
            'host'             => $this->host,
            'port'             => $this->port,
            'keyPrefix'        => $this->keyPrefix,
            'keySuffix'        => $this->keySuffix,
            'hashKey'          => $this->hashKey,
            'readOnly'         => $this->readOnly,
            'debuggerDisabled' => $this->debuggerDisabled,
        );
    }
}
