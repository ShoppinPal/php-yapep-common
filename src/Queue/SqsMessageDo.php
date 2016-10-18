<?php

namespace ShoppinPal\YapepCommon\Queue;

use ShoppinPal\YapepCommon\Serializer\ISerializer;

class SqsMessageDo
{

    /** @var string */
    protected $messageId;

    /** @var string */
    protected $receiptHandle;

    /** @var string */
    protected $body;

    /** @var string */
    protected $md5OfBody;

    /** @var array */
    protected $attributes;

    /** @var array */
    protected $messageAttributes;

    /** @var  ISerializer */
    protected $serializer;

    public function __construct(array $messageData, ISerializer $serializer)
    {
        $this->messageId         = (string)$messageData['MessageId'];
        $this->receiptHandle     = (string)$messageData['ReceiptHandle'];
        $this->body              = (string)$messageData['Body'];
        $this->md5OfBody         = (string)$messageData['MD5OfBody'];
        $this->attributes        = isset($messageData['Attributes']) ? $messageData['Attributes'] : [];
        $this->messageAttributes = isset($messageData['MessageAttributes']) ? $messageData['MessageAttributes'] : [];
        $this->serializer        = $serializer;
    }

    /**
     * @return string
     */
    public function getMessageId()
    {
        return $this->messageId;
    }

    /**
     * @return string
     */
    public function getReceiptHandle()
    {
        return $this->receiptHandle;
    }

    /**
     * @return string
     */
    public function getBody()
    {
        return $this->body;
    }

    /**
     * Returns the unserialized version of the body.
     *
     * @return mixed
     */
    public function getUnserializedBody()
    {
        return $this->serializer->unserialize($this->body);
    }

    /**
     * @return string
     */
    public function getMd5OfBody()
    {
        return $this->md5OfBody;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * Returns TRUE if the message has the specified message attribute, FALSE otherwise.
     *
     * @param string $attributeName
     *
     * @return bool
     */
    public function hasMessageAttribute($attributeName)
    {
        return isset($this->messageAttributes[$attributeName])
            && is_array($this->messageAttributes[$attributeName])
            && isset($this->messageAttributes[$attributeName]['DataType'])
            && $this->messageAttributes[$attributeName]['DataType'] == 'String'
            && isset($this->messageAttributes[$attributeName]['StringValue']);
    }

    /**
     * Returns the specified message attribute if it's set, or NULL if not set.
     *
     * @param string $attributeName
     *
     * @return string|null
     */
    public function getMessageAttribute($attributeName)
    {
        if ($this->hasMessageAttribute($attributeName)) {
            return null;
        }

        return $this->messageAttributes[$attributeName]['StringValue'];
    }

}
