<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Log;


use YapepBase\Config;
use YapepBase\Exception\ConfigException;
use YapepBase\File\FileHandlerPhp;
use YapepBase\Log\LoggerAbstract;
use YapepBase\Log\Message\IMessage;

/**
 * File logger class
 *
 * Logs every message in the specified file. Every message is a json object.
 * The messages are separated by new line.
 *
 * Configuration:
 *     <ul>
 *         <li>path: Path to the log file..</li>
 *     </ul>
 */
class FileLogger extends LoggerAbstract
{

    /**
     * @var string
     */
    protected $filePath;

    /**
     * @var FileHandlerPhp
     */
    protected $fileHandler;


    public function __construct(string $configName)
    {
        $config = Config::getInstance();

        $fullConfigName = 'resource.log.' . $configName . '.path';
        $this->filePath = $config->get($fullConfigName, false);
        if (empty($this->filePath)) {
            throw new ConfigException('Config entry "' . $fullConfigName . '" is missing');
        }

        $this->fileHandler = new FileHandlerPhp();

        if (!$this->fileHandler->checkIsPathExists($this->fileHandler->getParentDirectory($this->filePath))) {
            $this->fileHandler->makeDirectory($this->fileHandler->getParentDirectory($this->filePath), 0755, true);
        }

        if (!$this->fileHandler->checkIsPathExists($this->filePath)) {
            $this->fileHandler->touch($this->filePath);
        }
        elseif (!$this->fileHandler->checkIsFile($this->filePath) || !$this->fileHandler->checkIsWritable($this->filePath)) {
            throw new ConfigException('The given path "' . $this->filePath . '" is not a file or not writable!');
        }
    }


    protected function logMessage(IMessage $message)
    {
        $messageArray = [
            'tag'      => $message->getTag(),
            'priority' => $message->getPriority(),
            'message'  => $message->getMessage(),
            'fields'   => $message->getFields(),
        ];
        $messageToLog = json_encode($messageArray) . PHP_EOL;

        $this->fileHandler->write($this->filePath, $messageToLog, true);
    }

}
