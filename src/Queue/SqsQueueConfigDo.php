<?php

namespace ShoppinPal\YapepCommon\Queue;

class SqsQueueConfigDo
{
    /** @var int */
    protected $delaySeconds;

    /** @var int */
    protected $maximumMessageSizeBytes;

    /** @var int */
    protected $messageRetentionPeriodSeconds;

    /** @var int */
    protected $receiveMessageWaitTimeSeconds;

    /** @var int */
    protected $redriveMaxReceiveCount;

    /** @var string */
    protected $redriveDeadLetterTargetArn;

    /** @var int */
    protected $visibilityTimeoutSeconds;

    /**
     * SqsQueueConfigDo constructor.
     *
     * @param int    $delaySeconds
     * @param int    $maximumMessageSize
     * @param int    $messageRetentionPeriodSeconds
     * @param int    $receiveMessageWaitTimeSeconds
     * @param int    $redriveMaxReceiveCount
     * @param string $redriveDeadLetterTargetArn
     * @param int    $visibilityTimeoutSeconds
     */
    public function __construct(
        $delaySeconds,
        $maximumMessageSize,
        $messageRetentionPeriodSeconds,
        $receiveMessageWaitTimeSeconds,
        $redriveMaxReceiveCount,
        $redriveDeadLetterTargetArn,
        $visibilityTimeoutSeconds
    ) {
        $this->delaySeconds                  = $delaySeconds;
        $this->maximumMessageSizeBytes       = $maximumMessageSize;
        $this->messageRetentionPeriodSeconds = $messageRetentionPeriodSeconds;
        $this->receiveMessageWaitTimeSeconds = $receiveMessageWaitTimeSeconds;
        $this->redriveMaxReceiveCount        = $redriveMaxReceiveCount;
        $this->redriveDeadLetterTargetArn    = $redriveDeadLetterTargetArn;
        $this->visibilityTimeoutSeconds      = $visibilityTimeoutSeconds;
    }

    /**
     * @return array
     */
    public function getSqsAttributes()
    {
        $attributes = [
            'DelaySeconds'                  => $this->delaySeconds,
            'MaximumMessageSize'            => $this->maximumMessageSizeBytes,
            'MessageRetentionPeriod'        => $this->messageRetentionPeriodSeconds,
            'ReceiveMessageWaitTimeSeconds' => $this->receiveMessageWaitTimeSeconds,
            'VisibilityTimeout'             => $this->visibilityTimeoutSeconds,
        ];

        if ($this->redriveDeadLetterTargetArn && $this->redriveMaxReceiveCount) {
            $attributes['RedrivePolicy'] = json_encode([
                'maxReceiveCount'     => $this->redriveMaxReceiveCount,
                'deadLetterTargetArn' => $this->redriveDeadLetterTargetArn,
            ]);
        }

        foreach ($attributes as $key => $value) {
            if (null === $value) {
                unset($attributes[$key]);
            }
        }

        return $attributes;
    }

    /**
     * @return int
     */
    public function getDelaySeconds()
    {
        return $this->delaySeconds;
    }

    /**
     * @return int
     */
    public function getMaximumMessageSizeBytes()
    {
        return $this->maximumMessageSizeBytes;
    }

    /**
     * @return int
     */
    public function getMessageRetentionPeriodSeconds()
    {
        return $this->messageRetentionPeriodSeconds;
    }

    /**
     * @return int
     */
    public function getReceiveMessageWaitTimeSeconds()
    {
        return $this->receiveMessageWaitTimeSeconds;
    }

    /**
     * @return int
     */
    public function getRedriveMaxReceiveCount()
    {
        return $this->redriveMaxReceiveCount;
    }

    /**
     * @return string
     */
    public function getRedriveDeadLetterTargetArn()
    {
        return $this->redriveDeadLetterTargetArn;
    }

    /**
     * @return int
     */
    public function getVisibilityTimeoutSeconds()
    {
        return $this->visibilityTimeoutSeconds;
    }


}
