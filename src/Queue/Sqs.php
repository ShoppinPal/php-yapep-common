<?php

namespace ShoppinPal\YapepCommon\Queue;

use Aws\Sqs\Exception\SqsException;
use Aws\Sqs\SqsClient;
use ShoppinPal\YapepCommon\Serializer\ISerializer;
use ShoppinPal\YapepCommon\Serializer\JsonSerializer;
use ShoppinPal\YapepCommon\Serializer\SerializeSerializer;
use ShoppinPal\YapepCommon\Serializer\StringSerializer;
use YapepBase\Config;
use YapepBase\Exception\ParameterException;

class Sqs
{
    /** Key of the original queue name message attribute */
    const ATTRIBUTE_KEY_ORIGINAL_QUEUE_NAME = 'original_queue_name';

    /** Serializer: serialize. Uses the serialize()/unserialize() functions to serialize the data. */
    const SERIALIZER_SERIALIZE = 'serialize';

    /** Serializer: JSON. Data will be serialized to JSON format. */
    const SERIALIZER_JSON = 'json';

    /** Serializer: string. The data will simply be cast to a string, which may result in data loss! */
    const SERIALIZER_STRING = 'string';

    /**
     * Cache for storing queue name => queue URL mappings
     *
     * @var array
     */
    private $queueUrlCache = [];

    /**
     * @var SqsClient
     */
    protected $sqsClient;

    /**
     * @var string
     */
    protected $defaultSerializer;

    /** @var SqsQueueConfigHandler */
    protected $sqsQueueConfigHandler;

    /**
     * Sqs constructor.
     */
    public function __construct($configName)
    {
        $config       = Config::getInstance();
        $region       = $config->get('commonResource.sqs.' . $configName . '.region', '');
        $clientConfig = [
            'version'     => $config->get('commonResource.sqs.' . $configName . '.version', 'latest'),
            'credentials' => [
                'key'    => $config->get('commonResource.sqs.' . $configName . '.accessKeyId'),
                'secret' => $config->get('commonResource.sqs.' . $configName . '.accessSecret'),
            ],
        ];

        $endpoint = $config->get('commonResource.sqs.' . $configName . '.endpoint', '');
        if ($endpoint) {
            $clientConfig['endpoint'] = $endpoint;
        }

        if ($region) {
            $clientConfig['region'] = $region;
        }

        $this->sqsClient             = new SqsClient($clientConfig);
        $this->sqsQueueConfigHandler = new SqsQueueConfigHandler();

        $this->defaultSerializer = $config->get(
            'commonResource.sqs.' . $configName . '.serializer',
            self::SERIALIZER_JSON
        );
    }

    /**
     * Sends a message to the queue
     *
     * @param string $queueConfigName
     * @param mixed  $messageBody
     * @param int    $delaySeconds
     * @param array  $messageAttributes
     *
     * @return string
     */
    public function sendMessage(
        $queueConfigName,
        $messageBody,
        $delaySeconds = 0,
        array $messageAttributes = []
    )
    {
        $queueName  = $this->sqsQueueConfigHandler->getNameForQueue($queueConfigName);
        $serializer = $this->sqsQueueConfigHandler->getSerializerForQueue($queueConfigName, $this->defaultSerializer);

        $messageAttributes = array_merge($messageAttributes, [
            self::ATTRIBUTE_KEY_ORIGINAL_QUEUE_NAME => $queueName
        ]);

        $sqsMessageAttributes = [];

        foreach ($messageAttributes as $key => $value) {
            $sqsMessageAttributes[$key] = [
                'StringValue' => $value,
                'DataType'    => 'String',
            ];
        }

        $response = $this->sqsClient->sendMessage([
            'QueueUrl'          => $this->getQueueUrlAndCreateIfNotExists($queueName, $queueConfigName),
            'MessageBody'       => $this->getSerializer($serializer)->serialize($messageBody),
            'DelaySeconds'      => $delaySeconds,
            'MessageAttributes' => $sqsMessageAttributes
        ]);

        return $response->get('MessageId');
    }

    /**
     * Receives one or more messages from the specified queue.
     *
     * @param string $queueConfigName
     * @param int    $waitTimeSeconds
     * @param int    $maxNumberOfMessages
     * @param array  $attributeNames
     * @param array  $messageAttributeNames
     * @param null   $visibilityTimeout
     *
     * @return SqsMessageDo[]
     */
    public function receiveMessage(
        $queueConfigName,
        $waitTimeSeconds = 0,
        $maxNumberOfMessages = 10,
        array $attributeNames = [],
        array $messageAttributeNames = ['All'],
        $visibilityTimeout = null
    )
    {
        $queueName  = $this->sqsQueueConfigHandler->getNameForQueue($queueConfigName);
        $queueUrl   = $this->getQueueUrlAndCreateIfNotExists($queueName, $queueConfigName);

        $args     = [
            'QueueUrl'              => $queueUrl,
            'WaitTimeSeconds'       => $waitTimeSeconds,
            'MaxNumberOfMessages'   => $maxNumberOfMessages,
            'AttributeNames'        => $attributeNames,
            'MessageAttributeNames' => $messageAttributeNames,
        ];

        if (null !== $visibilityTimeout) {
            $args['VisibilityTimeout'] = $visibilityTimeout;
        }

        $response = $this->sqsClient->receiveMessage($args);

        $serializer = $this->sqsQueueConfigHandler->getSerializerForQueue($queueConfigName, $this->defaultSerializer);

        $messages = [];

        $receivedMessages = $response->get('Messages');

        if ($receivedMessages) {
            foreach ($receivedMessages as $message) {
                $messages[] = new SqsMessageDo($message, $this->getSerializer($serializer));
            }
        }

        return $messages;
    }

    /**
     * Delete a message from the queue (after it was received).
     *
     * @param string $queueConfigName
     * @param string $receiptHandle
     *
     * @return void
     */
    public function deleteMessage($queueConfigName, $receiptHandle)
    {
        $queueName  = $this->sqsQueueConfigHandler->getNameForQueue($queueConfigName);
        $queueUrl   = $this->getQueueUrlAndCreateIfNotExists($queueName, $queueConfigName);

        $this->sqsClient->deleteMessage([
            'QueueUrl'      => $queueUrl,
            'ReceiptHandle' => $receiptHandle,
        ]);
    }

    /**
     * Purges all the messages from the queue
     *
     * @param string $queueConfigName
     *
     * @return void
     */
    public function purge($queueConfigName)
    {
        $queueName  = $this->sqsQueueConfigHandler->getNameForQueue($queueConfigName);
        $queueUrl   = $this->getQueueUrlAndCreateIfNotExists($queueName, $queueConfigName);

        $this->sqsClient->purgeQueue([
            'QueueUrl' => $queueUrl
        ]);
    }

    /**
     * Returns the queue URL for the specified queue, and it will create it if it doesn't exist.
     *
     * @param string $queueName
     * @param string $queueConfigName
     *
     * @return string
     */
    protected function getQueueUrlAndCreateIfNotExists($queueName, $queueConfigName)
    {
        if (isset($this->queueUrlCache[$queueName])) {
            return $this->queueUrlCache[$queueName];
        }

        $client = $this->sqsClient;

        try {
            $response = $client->getQueueUrl([
                'QueueName' => $queueName,
            ]);

            $queueUrl =  (string)$response->get('QueueUrl');
        } catch (SqsException $e) {
            if ('AWS.SimpleQueueService.NonExistentQueue' != $e->getAwsErrorCode()) {
                // This exception is not for a non existing queue, re-throw it
                throw $e;
            }

            // The queue doesn't exist, create it
            $response = $client->createQueue([
                'QueueName'  => $queueName,
                'Attributes' => $this->sqsQueueConfigHandler->getNewQueueAttributes($queueConfigName)->getSqsAttributes(),
            ]);

            $queueUrl = (string)$response->get('QueueUrl');
        }

        $this->queueUrlCache[$queueName] = $queueUrl;

        return $queueUrl;
    }

    /**
     * Returns a serializer instance
     *
     * @param string $serializer The serializer type ({@uses self::SERIALIZER_*}) or the fully qualified class name
     *                           of a class that implements ISerializer.
     *
     * @return ISerializer
     *
     * @throws ParameterException If the requested serializer is not valid
     */
    protected function getSerializer($serializer)
    {
        switch ($serializer) {
            case self::SERIALIZER_SERIALIZE:
                $serializerClassName = SerializeSerializer::class;
                break;

            case self::SERIALIZER_JSON:
                $serializerClassName = JsonSerializer::class;
                break;

            case self::SERIALIZER_STRING:
                $serializerClassName = StringSerializer::class;
                break;

            default:
                $serializerClassName = $serializer;
                break;
        }

        if (!class_exists($serializerClassName)) {
            throw new ParameterException('The specified serializer class can not be found: ' . $serializerClassName);
        }

        $serializerObject = new $serializerClassName();

        if (!($serializerObject instanceof ISerializer)) {
            throw new ParameterException(
                'The specified serializer class "' . get_class($serializerObject)
                . '" does not implement the ISerializer interface: ' . $serializerClassName
            );
        }

        return $serializerObject;
    }
}
