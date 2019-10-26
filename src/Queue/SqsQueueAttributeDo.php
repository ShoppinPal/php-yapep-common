<?php

namespace ShoppinPal\YapepCommon\Queue;

class SqsQueueAttributeDo
{

    protected const ATTRIBUTE_MAP = [
        'Policy'                                => 'policy',
        'VisibilityTimeout'                     => 'visibilityTimeout',
        'MaximumMessageSize'                    => 'maxiumMessageSize',
        'MessageRetentionPeriod'                => 'maximumRetentionPeriod',
        'ApproximateNumberOfMessages'           => 'approximateNumberOfMessages',
        'ApproximateNumberOfMessagesNotVisible' => 'approximateNumberOfMessagesNotVisible',
        'CreatedTimestamp'                      => 'createdTimestamp',
        'LastModifiedTimestamp'                 => 'lastModifiedTimestamp',
        'QueueArn'                              => 'queueArn',
        'ApproximateNumberOfMessagesDelayed'    => 'approximateNumberOfMessagedDelayed',
        'DelaySeconds'                          => 'delaySeconds',
        'ReceiveMessageWaitTimeSeconds'         => 'receiveMessageWaitTimeSeconds',
        'RedrivePolicy'                         => 'redrivePolicy',
        'FifoQueue'                             => 'fifoQueue',
        'ContentBasedDeduplication'             => 'contentBasedDeduplication',
        'KmsMasterKeyId'                        => 'kmsMasterKeyId',
        'KmsDataKeyReusePeriodSeconds'          => 'kmsDataKeyReusePeriodSeconds',
    ];

    /** @var int|null */
    protected $approximateNumberOfMessagedDelayed;

    /** @var int|null */
    protected $approximateNumberOfMessages;

    /** @var int|null */
    protected $approximateNumberOfMessagesNotVisible;

    /** @var bool|null */
    protected $contentBasedDeduplication;

    /** @var string|null */
    protected $createdTimestamp;

    /** @var int|null */
    protected $delaySeconds;

    /** @var bool|null */
    protected $fifoQueue;

    /** @var string|null */
    protected $kmsDataKeyReusePeriodSeconds;

    /** @var string|null */
    protected $kmsMasterKeyId;

    /** @var int|null */
    protected $lastModifiedTimestamp;

    /** @var int|null */
    protected $maximumRetentionPeriod;

    /** @var int|null */
    protected $maxiumMessageSize;

    /** @var string|null */
    protected $policy;

    /** @var string|null */
    protected $queueArn;

    /** @var int|null */
    protected $receiveMessageWaitTimeSeconds;

    /** @var string|null */
    protected $redrivePolicy;

    /** @var int|null */
    protected $visibilityTimeout;

    public function __construct(array $attributes)
    {
        foreach (self::ATTRIBUTE_MAP as $attributeName => $propertyName) {
            $this->$propertyName = $attributes[$attributeName] ?? null;
        }
    }

    public function getApproximateNumberOfMessagedDelayed(): ?int
    {
        return $this->approximateNumberOfMessagedDelayed;
    }

    public function getApproximateNumberOfMessages(): ?int
    {
        return $this->approximateNumberOfMessages;
    }

    public function getApproximateNumberOfMessagesNotVisible(): ?int
    {
        return $this->approximateNumberOfMessagesNotVisible;
    }

    public function getContentBasedDeduplication(): ?bool
    {
        return $this->contentBasedDeduplication;
    }

    public function getCreatedTimestamp(): ?string
    {
        return $this->createdTimestamp;
    }

    public function getDelaySeconds(): ?int
    {
        return $this->delaySeconds;
    }

    public function getFifoQueue(): ?bool
    {
        return $this->fifoQueue;
    }

    public function getKmsDataKeyReusePeriodSeconds(): ?string
    {
        return $this->kmsDataKeyReusePeriodSeconds;
    }

    public function getKmsMasterKeyId(): ?string
    {
        return $this->kmsMasterKeyId;
    }

    public function getLastModifiedTimestamp(): ?int
    {
        return $this->lastModifiedTimestamp;
    }

    public function getMaximumRetentionPeriod(): ?int
    {
        return $this->maximumRetentionPeriod;
    }

    public function getMaxiumMessageSize(): ?int
    {
        return $this->maxiumMessageSize;
    }

    public function getPolicy(): ?string
    {
        return $this->policy;
    }

    public function getQueueArn(): ?string
    {
        return $this->queueArn;
    }

    public function getReceiveMessageWaitTimeSeconds(): ?int
    {
        return $this->receiveMessageWaitTimeSeconds;
    }

    public function getRedrivePolicy(): ?string
    {
        return $this->redrivePolicy;
    }

    public function getVisibilityTimeout(): ?int
    {
        return $this->visibilityTimeout;
    }
}
