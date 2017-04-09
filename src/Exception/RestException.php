<?php
declare(strict_types=1);

namespace Www\Exception;

use YapepBase\Exception\Exception;

class RestException extends Exception
{

    const CODE_PARAMETER_ERROR = 'ParameterError';
    const CODE_UNAUTHENTICATED = 'UnauthenticatedError';
    const CODE_PAYMENT_REQUIRED = 'PaymentRequired';
    const CODE_UNAUTHORIZED = 'UnauthorizedError';
    const CODE_METHOD_NOT_SUPPORTED = 'MethodNotSupported';
    const CODE_INTERNAL_ERROR = 'InternalError';
    const CODE_REQUEST_ERROR = 'RequestError';

    const MSG_PARAMETER_ERROR = 'An invalid parameter was sent to the endpoint, or a required parameter is missing';
    const MSG_UNAUTHENTICATED = 'This endpoint requires authentication but no "Authorization" header is sent, or the token is invalid';
    const MSG_PAYMENT_REQUIRED = 'The authenticated account is in debt';
    const MSG_UNAUTHORIZED = 'The authenticated account is not authorized to perform the requested action';
    const MSG_METHOD_NOT_SUPPORTED = 'The requested endpoint exists, but does not support the specified method';
    const MSG_INTERNAL_ERROR = 'An internal error occured while serving the request. The error has been logged, please try your request again later';
    const MSG_REQUEST_ERROR = 'There is a problem with your request';

    const PARAM_ERROR_MISSING = 'missing';
    const PARAM_ERROR_DUPLICATE = 'duplicate';
    const PARAM_ERROR_INVALID = 'invalid';
    const PARAM_ERROR_MUST_BE_EMPTY = 'mustBeEmpty';

    /** @var string  */
    protected $errorCode;

    /** @var array */
    protected $params = [];

    public function __construct(
        string $errorCode,
        ?string $message = null,
        array $params = [],
        int $code = 0,
        ?\Exception $previous = null,
        ?mixed $data = null
    )
    {
        $this->errorCode = $errorCode;
        $this->params    = $params;

        $message = null === $message ? $this->getDefaultMessageForErrorCode() : $message;

        parent::__construct($message, $code, $previous, $data);
    }

    private function getDefaultMessageForErrorCode(): ?string
    {
        switch ($this->errorCode) {
            case self::CODE_PARAMETER_ERROR:
                return self::MSG_PARAMETER_ERROR;

            case self::CODE_UNAUTHENTICATED:
                return self::MSG_UNAUTHENTICATED;

            case self::CODE_PAYMENT_REQUIRED:
                return self::MSG_PAYMENT_REQUIRED;

            case self::CODE_UNAUTHORIZED:
                return self::MSG_UNAUTHORIZED;

            case self::CODE_METHOD_NOT_SUPPORTED:
                return self::MSG_METHOD_NOT_SUPPORTED;

            case self::CODE_INTERNAL_ERROR:
                return self::MSG_INTERNAL_ERROR;

            case self::CODE_REQUEST_ERROR:
                return self::MSG_REQUEST_ERROR;
        }

        return null;
    }

    public function getDefaultHttpStatusCode(): ?int
    {
        switch ($this->errorCode) {
            case self::CODE_REQUEST_ERROR:
            case self::CODE_PARAMETER_ERROR:
                return 400;

            case self::CODE_UNAUTHENTICATED:
                return 401;

            case self::CODE_PAYMENT_REQUIRED:
                return 402;

            case self::CODE_UNAUTHORIZED:
                return 403;

            case self::CODE_METHOD_NOT_SUPPORTED:
                return 405;

            case self::CODE_INTERNAL_ERROR:
            default:
                return 500;
        }
    }

    /**
     * @return string
     */
    public function getErrorCode(): string
    {
        return $this->errorCode;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return $this->params;
    }
}
