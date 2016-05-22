<?php
namespace ShoppinPal\YapepCommon\Log;

use YapepBase\Exception\ParameterException;

/**
 * Helper for log level handling
 */
class LogLevelHelper
{

    /**
     * Contains the valid log levels and their description texts.
     *
     * @var array
     */
    protected $logLevels = [
        LOG_DEBUG   => ['DEBUG'],
        LOG_INFO    => ['INFO'],
        LOG_NOTICE  => ['NOTICE'],
        LOG_WARNING => ['WARN', 'WARNING'],
        LOG_ERR     => ['ERR', 'ERROR'],
        LOG_CRIT    => ['CRIT', 'CRITICAL'],
        LOG_ALERT   => ['ALERT'],
        LOG_EMERG   => ['EMERG', 'EMERGENCY'],
    ];

    /**
     * Returns the log level ID from the textual description.
     *
     * @param string $text The log level description
     *
     * @return int The log level ID. {@uses LOG_*}
     * @throws ParameterException If the description is not known.
     */
    public function getLevelFromText($text)
    {
        $convertedText = strtoupper($text);

        foreach ($this->logLevels as $logLevel => $descriptions) {
            if (in_array($convertedText, $descriptions, true)) {
                return $logLevel;
            }
        }

        throw new ParameterException(sprintf('Unknown log level text: "%s"', $text));
    }

    /**
     * Returns the default log level description for the specified log level.
     *
     * @param int $logLevel The log level ID. {@uses LOG_*}
     *
     * @return string The default log level description.
     * @throws ParameterException If the log level ID is not valid.
     */
    public function getTextFromLevel($logLevel)
    {
        if (!array_key_exists($logLevel, $this->logLevels)) {
            throw new ParameterException(sprintf('Unknown log level ID: "%s"', $logLevel));
        }

        return $this->logLevels[$logLevel][0];
    }

}
