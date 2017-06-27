<?php
declare(strict_types=1);

namespace ShoppinPal\YapepCommon\Log;

use ShoppinPal\YapepCommon\Log\Message\GenericMessage;
use YapepBase\Log\ILogger;

class GenericLogger
{
    /** @var ILogger  */
    protected $logger;

    /** @var string */
    protected $defaultLogTag;

    public function __construct(ILogger $logger, string $defaultLogTag)
    {
        $this->logger        = $logger;
        $this->defaultLogTag = $defaultLogTag;
    }

    /**
     * Creates a new instance of the LogHelper with the new default log tag.
     *
     * @param string $defaultLogTag
     *
     * @return GenericLogger
     */
    public function cloneWithNewDefaultLogTag(string $defaultLogTag): GenericLogger
    {
        return new static($this->logger, $defaultLogTag);
    }

    public function getLogger(): ILogger
    {
        return $this->logger;
    }

    public function log(string $message, int $priority = LOG_INFO, string $logTag = null)
    {
        $this->logger->log(new GenericMessage(
            $message,
            $priority,
            $logTag ?: $this->defaultLogTag
        ));
    }

}
