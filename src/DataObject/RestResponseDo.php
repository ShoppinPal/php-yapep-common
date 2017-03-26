<?php

namespace Www\DataObject;

class RestResponseDo
{
    /** @var int */
    public $statusCode = 200;

    /** @var array */
    public $headers = [];

    /** @var mixed */
    public $payload;

    /**
     * @param string $header
     * @param string $value
     *
     * @return $this
     */
    public function addHeader($header, $value): RestResponseDo
    {
        $this->headers[$header] = $value;

        return $this;
    }
}
