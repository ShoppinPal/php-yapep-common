<?php

namespace ShoppinPal\YapepCommon\Queue;

/**
 * Wrapper around the SQS instance to implement the simpler queue interface.
 *
 * Use this class if you don't need the more advanced features of SQS and want the option to swap out SQS with Kestrel
 * for example for dev.
 */
class SqsQueue implements IQueue
{
    /** @var Sqs */
    protected $sqs;

    /**
     * @param string $configName
     */
    public function __construct($configName)
    {
        $this->sqs = new Sqs($configName);
    }

    public function sendMessage($queueConfigName, $messageBody)
    {
        $this->sqs->sendMessage($queueConfigName, $messageBody);
    }

    public function receiveMessage($queueConfigName, $waitTimeSeconds = 0)
    {
        $messages = $this->sqs->receiveMessage($queueConfigName, $waitTimeSeconds, 1);

        if (empty($messages)) {
            return null;
        }

        $message = reset($messages);

        return new QueueMessageDo($message->getUnserializedBody(), $message->getReceiptHandle());
    }

    public function deleteMessage($queueConfigName, $deleteId)
    {
        $this->sqs->deleteMessage($queueConfigName, $deleteId);
    }

}
