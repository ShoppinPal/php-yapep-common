<?php

namespace ShoppinPal\YapepCommon\Queue;

class QueueMessage
{
    /** @var mixed */
    protected $messageBody;

    /** @var null|string */
    protected $deleteId;

    /**
     * @param mixed       $messageBody
     * @param string|null $deleteId
     */
    public function __construct($messageBody, $deleteId = null)
    {
        $this->messageBody = $messageBody;
        $this->deleteId = $deleteId;
    }

    /**
     * @return mixed
     */
    public function getMessageBody()
    {
        return $this->messageBody;
    }

    /**
     * @return null|string
     */
    public function getDeleteId()
    {
        return $this->deleteId;
    }
}
