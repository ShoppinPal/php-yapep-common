<?php
namespace ShoppinPal\YapepCommon\ErrorHandler;

use YapepBase\ErrorHandler\DebugDataCreator;

class BlacklistingDebugDataCreator extends DebugDataCreator
{
    const BLACKLISTED_CLASS_PATTERNS = [
        '/(^|\\\\)Aws(\\\\|$)/i',
        '/(^|\\\\)Amazon(\\\\|$)/i',
        '/(^|\\\\)Sqs/i',
        '/(^|\\\\)S3/i',
        '/(^|\\\\)Config(\\\\|$)/i',
    ];

    const MAX_DEPTH = 10;

    /**
     * @param string $errorId
     * @param string $errorMessage
     * @param array  $backTrace
     * @param array  $context
     *
     * @return string
     */
    protected function getDebugData($errorId, $errorMessage, array $backTrace = array(), array $context = array())
    {
        $context = $this->removeBlackListedClassesFromArray($context);
        $backTrace = $this->removeBlackListedClassesFromArray($backTrace);

        return parent::getDebugData(
            $errorId,
            $errorMessage,
            $backTrace,
            $context
        );
    }

    /**
     * @param array|\Iterator|\IteratorAggregate $data
     * @param int                                $recursionDepth
     *
     * @return array
     */
    protected function removeBlackListedClassesFromArray($data, $recursionDepth = 0)
    {
        foreach ($data as $key => $value)
        {
            if (is_object($value)) {
                foreach (self::BLACKLISTED_CLASS_PATTERNS as $pattern) {
                    $class = get_class($value);
                    if (preg_match($pattern, $class)) {
                        $value = $data[$key] = '*** Removed by blacklist. Class of ' . $class.  ' ***';
                        break;
                    }
                }
            }

            if ($recursionDepth >= self::MAX_DEPTH) {
                // We've reached the max depth, no more recursion
                continue;
            }

            // Run recursively over arrays and objects that are iterable and have ArrayAccess
            if (
                is_array($value)
                || (
                    is_object($value)
                    && $value instanceof \ArrayAccess
                    && (
                        $value instanceof \Iterator
                        || $value instanceof \IteratorAggregate
                    )
                )
            ) {
                $data[$key] = $this->removeBlackListedClassesFromArray($value, $recursionDepth + 1);
            }
        }

        return $data;
    }
}
