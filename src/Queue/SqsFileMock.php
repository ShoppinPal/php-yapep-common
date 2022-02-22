<?php

namespace ShoppinPal\YapepCommon\Queue;

use YapepBase\Exception\ParameterException;

/**
 * File based mock for SQS.
 */
class SqsFileMock extends Sqs
{
    const KEY_ID              = 'id';
    const KEY_BODY            = 'body';
    const KEY_VISIBLE_AFTER   = 'visibleAfter';
    const KEY_ATTRIBUTES      = 'attributes';
    const KEY_RECEIPT_HANDLES = 'receiptHandles';

    const DEFAULT_VISIBILITY_TIMEOUT = 5;

    /**
     * @var string
     */
    protected $directory;

    /**
     * SqsFileMock constructor.
     *
     * @param string $directory
     *
     * @throws ParameterException
     */
    public function __construct($directory)
    {
        if (empty($directory)) {
            throw new ParameterException('No directory was specified: ' . $directory);
        }

        $directory = realpath($directory);

        if (!file_exists($directory)) {
            throw new ParameterException('The specified directory does not exist: ' . $directory);
        }

        if (!is_dir($directory)) {
            throw new ParameterException('$The specified directory is not a director: ' . $directory);
        }

        if (!is_readable($directory)) {
            throw new ParameterException('The specified directory is not readable: ' . $directory);
        }

        if (!is_writable($directory)) {
            throw new ParameterException('The specified directory is not writable: ' . $directory);
        }

        $this->directory = $directory;
    }

    /**
     * Removes all queues from the queue directory.
     *
     * @return void
     */
    public function clearQueueDir()
    {
        foreach (scandir($this->directory) as $file) {
            if (preg_match('/\.json$/', $file) && is_file($this->directory . DIRECTORY_SEPARATOR . $file)) {
                unlink($this->directory . DIRECTORY_SEPARATOR . $file);
            }
        }
    }

    /**
     * Returns the name of all the queues.
     *
     * @return array
     */
    public function getQueueNames()
    {
        $files = [];

        foreach (scandir($this->directory) as $file) {
            if (preg_match('/^(.+)\.json$/', $file, $matches) && is_file(
                    $this->directory . DIRECTORY_SEPARATOR . $file
                )
            ) {
                $files [] = $matches[1];
            }
        }

        return $files;
    }

    /**
     * Returns the number of messages in the specified queue.
     *
     * @param string $queueName
     *
     * @return int
     */
    public function getNumberOfMessagesInQueue($queueName)
    {
        return count(
            json_decode(
                file_get_contents($this->directory . DIRECTORY_SEPARATOR . $queueName . '.json'),
                true
            )
        );
    }

    /**
     * @inheritdoc
     */
    public function sendMessage(
        $queueConfigName,
        $messageBody,
        $delaySeconds = 0,
        array $messageAttributes = []
    ) {
        $id = uniqid();

        $this->modifyQueueFile(
            $queueConfigName,
            function (array $content) use ($id, $messageBody, $delaySeconds, $messageAttributes) {
                $content[] = [
                    self::KEY_ID              => $id,
                    self::KEY_BODY            => $this->getSerializer(self::SERIALIZER_JSON)->serialize($messageBody),
                    self::KEY_ATTRIBUTES      => $messageAttributes,
                    self::KEY_VISIBLE_AFTER   => time() + $delaySeconds,
                    self::KEY_RECEIPT_HANDLES => [],
                ];

                return $content;
            }
        );

        return $id;
    }

    /**
     * @inheritdoc
     */
    public function receiveMessage(
        $queueConfigName,
        $waitTimeSeconds = 0,
        $maxNumberOfMessages = 10,
        array $attributeNames = [],
        array $messageAttributeNames = ['All'],
        $visibilityTimeout = null
    ) {
        $receiptHandle = uniqid('receipt_');

        $messages = [];

        $visibleAfter = time() + ($visibilityTimeout ?: self::DEFAULT_VISIBILITY_TIMEOUT);

        $this->modifyQueueFile(
            $queueConfigName,
            function (array $content) use ($receiptHandle, $visibleAfter, &$messages, $maxNumberOfMessages) {
                $currentTime = time();
                foreach ($content as $index => $message) {
                    if (count($messages) >= $maxNumberOfMessages) {
                        break;
                    }

                    if ($message[self::KEY_VISIBLE_AFTER] > $currentTime) {
                        continue;
                    }

                    foreach ($message[self::KEY_RECEIPT_HANDLES] as $visibilityTimeout) {
                        if ($visibilityTimeout > $currentTime) {
                            continue 2;
                        }
                    }

                    $content[$index][self::KEY_RECEIPT_HANDLES][$receiptHandle] = $visibleAfter;

                    $messageData = [
                        'MessageId'         => $message[self::KEY_ID],
                        'ReceiptHandle'     => $receiptHandle,
                        'Body'              => $message[self::KEY_BODY],
                        'MD5OfBody'         => md5($message[self::KEY_BODY]),
                        'MessageAttributes' => $message[self::KEY_ATTRIBUTES],
                    ];

                    $messages[] = new SqsMessageDo($messageData, $this->getSerializer(self::SERIALIZER_JSON));
                }

                return $content;
            }
        );

        return $messages;
    }

    /**
     * @inheritdoc
     */
    public function deleteMessage($queueConfigName, $receiptHandle)
    {
        $this->modifyQueueFile(
            $queueConfigName,
            function (array $content) use ($receiptHandle) {
                foreach ($content as $index => $message) {
                    if (array_key_exists($receiptHandle, $message[self::KEY_RECEIPT_HANDLES])) {
                        unset($content[$index]);
                        break;
                    }
                }

                return $content;
            }
        );
    }

    /**
     * @param string $queueConfigName
     * @param callable $contentModifierCallback
     *
     * @return void
     */
    protected function modifyQueueFile($queueConfigName, callable $contentModifierCallback)
    {
        $filePath = $this->directory . DIRECTORY_SEPARATOR . $this->getFileNameFromQueueConfigName($queueConfigName);

        if (!file_exists($filePath)) {
            touch($filePath);
            chmod($filePath, 0666);
        }

        $handle = fopen($filePath, 'a+');

        if (!flock($handle, LOCK_EX | LOCK_NB)) {
            fclose($handle);
            throw new \RuntimeException('Unable to acquire lock on the queue file');
        }

        fseek($handle, 0);

        $content = '';
        while (!feof($handle)) {
            $content .= fread($handle, 1024);
        }

        $decodedContent = ('' == $content) ? [] : json_decode($content, true);

        $newContent = $contentModifierCallback($decodedContent);

        fseek($handle, 0);
        ftruncate($handle, 0);

        fwrite($handle, json_encode(array_values($newContent), JSON_PRETTY_PRINT));

        flock($handle, LOCK_UN);
        fclose($handle);
    }

    /**
     * @param string $queueConfigName
     *
     * @return string
     */
    protected function getFileNameFromQueueConfigName($queueConfigName)
    {
        return preg_replace('/[^-_a-zA-Z0-9]+/', '_', $queueConfigName) . '.json';
    }
}
