<?php
namespace ShoppinPal\YapepCommon\Log\Message;
use YapepBase\Log\Message\MessageAbstract;


/**
 * Generic log message.
 *
 * If using data fields, use a specific log message class instead.
 */
class GenericMessage extends MessageAbstract
{
    /**
     * The log tag.
     *
     * @var string
     */
    protected $tag;

    /**
     * Constructor.
     *
     * @param string $message  The message.
     * @param int    $priority The priority of the message.
     * @param string $tag      The tag
     */
    public function __construct($message, $priority, $tag)
    {
        $this->message  = (string)$message;
        $this->priority = $priority;
        $this->tag      = $tag;
    }

    /**
     * Returns the log tag.
     *
     * @return string
     */
    public function getTag()
    {
        return $this->tag;
    }
}
