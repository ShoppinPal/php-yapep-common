<?php

declare(strict_types=1);

namespace Www\Controller;

use Www\DataObject\RestResponseDo;
use Www\Exception\RestException;
use YapepBase\Config;
use YapepBase\Controller\HttpController;
use YapepBase\Exception\ControllerException;
use YapepBase\Mime\MimeType;
use YapepBase\Request\IRequest;
use YapepBase\Response\IResponse;
use YapepBase\View\RestTemplate;

abstract class RestApiController extends HttpController
{
    /** @var RestResponseDo */
    protected $restResponseDo;

    /** @var string */
    private $requestBody;

    /** @var array|null */
    private $requestData;

    public function __construct(IRequest $request, IResponse $response)
    {
        parent::__construct($request, $response);
        $this->restResponseDo = new RestResponseDo();
    }

    public function run($action)
    {
        try {
            $this->requestBody = file_get_contents('php://input');

            if ($this->requestBody) {
                $this->requestData = json_decode($this->requestBody, true);
            }

            // Initialize the content type to JSON
            $this->response->setContentType(MimeType::JSON);

            $methodName = $this->getActionPrefix() . $action;
            if (!method_exists($this, $methodName)) {
                throw new RestException(
                    RestException::CODE_METHOD_NOT_SUPPORTED,
                    'The method ' . $this->request->getMethod() . ' is not supported by this endpoint'
                );
            }

            parent::run($action);
        } catch (RestException $e) {
            $view = new RestTemplate();
            if ($this->response->getStatusCode() < 400) {
                $this->response->setStatusCode($e->getDefaultHttpStatusCode());
            }

            if (401 == $this->response->getStatusCode() && !$this->response->hasHeader('WWW-Authenticate')) {
                $this->response->setHeader('WWW-Authenticate', 'Session realm="Please provide the session token"');
            }

            if (405 == $this->response->getStatusCode() && !$this->response->hasHeader('Allow')) {
                $this->response->setHeader('Allow', implode(', ', $this->getValidMethodsForAction($action)));
            }

            $view->setContent($this->getErrorResponse($e->getErrorCode(), $e->getMessage(), $e->getParams()));
            $view->setContentType($this->response->getContentType());

            $this->response->setBody($view);
        } catch (\Exception $e) {
            trigger_error(
                'Unhandled exception of ' . get_class($e) . ' while serving api response. Error: ' . $e->getMessage(),
                E_USER_ERROR
            );

            $view = new RestTemplate();
            $this->response->setStatusCode(500);
            $view->setContent(
                $this->getErrorResponse(RestException::CODE_INTERNAL_ERROR, RestException::MSG_INTERNAL_ERROR)
            );
            $view->setContentType($this->response->getContentType());

            $this->response->setBody($view);
        }
    }

    /**
     * Runs the action and returns the result as an ViewAbstract instance.
     *
     * @param string $methodName   The name of the method that contains the action.
     *
     * @return string|\YapepBase\View\RestTemplate   The result view or the rendered output.
     *
     * @throws \YapepBase\Exception\ControllerException   On controller specific error. (eg. action not found)
     * @throws \YapepBase\Exception\Exception             On framework related errors.
     * @throws \Exception                                 On non-framework related errors.
     */
    protected function runAction($methodName)
    {
        $view = new RestTemplate();
        $result = $this->$methodName();

        $view->setContentType($this->response->getContentType());

        foreach ($this->restResponseDo->headers as $header => $value) {
            $this->response->addHeader($header, $value);
        }

        $this->response->setStatusCode($this->restResponseDo->statusCode);

        if (null === $result) {
            if (null === $this->restResponseDo->payload) {
                return '';
            } else {
                $view->setContent($this->restResponseDo->payload);
            }
        } elseif (is_array($result)) {
            $view->setRootNode(Config::getInstance()->get('system.rest.xmlRootNode', 'rest'));
            $view->setContent($result);
            return $view;
        } elseif (!is_string($result) && !($result instanceof RestTemplate)) {
            throw new ControllerException('The received result is not a RestTemplate or an array or string',
                ControllerException::ERR_INVALID_ACTION_RETURN_VALUE);
        }

        return $result;
    }

    private function getErrorResponse(string $errorCode, string $errorDetails, array $params = []): array
    {
        $result = [
            'errorCode' => $errorCode,
            'errorDescription' => $errorDetails,
        ];

        if (!empty($params)) {
            $result['params'] = $params;
        }

        return $result;
    }


    protected function getActionPrefix(): string
    {
        return strtolower($this->request->getMethod());
    }

    private function getValidMethodsForAction(string $action)
    {
        $methods = [];
        foreach (get_class_methods($this) as $methodName) {
            if (preg_match('/^([a-z]+)' . preg_quote(ucfirst($action), '/') .'$/', $methodName, $matches)) {
                $methods[] = strtoupper($matches[1]);
            }
        }

        return $methods;
    }

    protected function getRequestBody(): string
    {
        return $this->requestBody;
    }

    protected function getRequestData(): ?array
    {
        return $this->requestData;
    }

    protected function getFieldFromRequestData(string $key, $defaultValue = null)
    {
        return (is_array($this->requestData) && array_key_exists($key, $this->requestData))
            ? $this->requestData[$key]
            : $defaultValue;
    }
}
