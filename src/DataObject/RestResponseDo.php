<?php
declare(strict_types=1);

namespace ShoppinPal\YapepCommon\DataObject;

class RestResponseDo
{
    /** @var int */
    public $statusCode = 200;

    /** @var array */
    public $headers = [];

    /** @var mixed */
    public $payload;

    public function addHeader(string $header, string $value): RestResponseDo
    {
        $this->headers[$header] = $value;

        return $this;
    }
}
