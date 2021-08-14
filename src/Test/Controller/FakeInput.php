<?php
declare(strict_types=1);

namespace ShoppinPal\YapepCommon\Test\Controller;

use ShoppinPal\YapepCommon\Controller\PhpInput;

class FakeInput extends PhpInput
{
    /** @var string */
    private $stdIn;

    public function __construct(string $stdIn)
    {
        $this->stdIn = $stdIn;
    }

    public function getStdIn(): string
    {
        return $this->stdIn;
    }

}
