<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Test\Integration;


use ShoppinPal\YapepCommon\Bootstrap\BootstrapAbstract;
use YapepBase\Communication\CurlHttpRequest;
use YapepBase\Communication\CurlHttpRequestResult;


class RequestHandler
{
    /**
     * @var string
     */
    protected $responseBody;

    /**
     * @var string
     */
    protected $responseHeaders;

    /**
     * @var array
     */
    protected $sentParams = array();

    /**
     * @var string
     */
    protected $calledUrl;

    public function callUrl(string $url, string $method = CurlHttpRequest::METHOD_GET, array $params = [], $storeResponse = true): CurlHttpRequestResult
    {
        $params[BootstrapAbstract::GET_PARAM_NAME_TESTING_MODE] = 1;
        $this->sentParams = $params;
        $this->calledUrl = $url;

        $request = new CurlHttpRequest();
        $request->setUrl($url);
        $request->setMethod($method);
        $request->setPayload($params, CurlHttpRequest::PAYLOAD_TYPE_QUERY_STRING_ARRAY);

        $result = $request->send();
        if ($storeResponse) {
            $this->responseHeaders = $result->getResponseHeaders();
            $this->responseBody = $result->getResponseBody();
        }

        return $result;
    }

    public function callRestApi(
        string $url,
        string $method = CurlHttpRequest::METHOD_GET,
        array $params = [],
        array $headers = [],
        $storeResponse = true
    ): CurlHttpRequestResult
    {
        $url = $url
            . (strpos($url, '?') === false ? '?' : '&')
            . BootstrapAbstract::GET_PARAM_NAME_TESTING_MODE . '=1';

        $request = new CurlHttpRequest();
        $request->setUrl($url);
        $request->setMethod($method);
        $request->addHeader('Content-Type: application/json');
        if (!empty($this->sessionId)) {
            $request->addHeader('Authorization: Session ' . $this->sessionId);
        }
        foreach ($headers as $header) {
            $request->addHeader($header);
        }

        $request->setPayload(json_encode($params), CurlHttpRequest::PAYLOAD_TYPE_RAW);

        $result = $request->send();
        if ($storeResponse) {
            $this->responseHeaders = $result->getResponseHeaders();
            $this->responseBody = $result->getResponseBody();
        }

        return $result;
    }

    public function getResponseBody(): array
    {
        return json_decode($this->responseBody, true);
    }

    public function getResponseHeaders(): string
    {
        return $this->responseHeaders;
    }


    protected function formatDataForPost(array $post, array &$output, string $paramNamePrefix = null)
    {
        foreach ($post as $key => $value) {
            $currentKey = !empty($paramNamePrefix) ? $paramNamePrefix . '[' . $key . ']' : $key;

            if (is_array($value) || is_object($value)) {
                $this->formatDataForPost($value, $output, $currentKey);
            }
            else {
                $output[$currentKey] = $value;
            }
        }
    }
}
