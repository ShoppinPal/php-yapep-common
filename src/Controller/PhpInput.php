<?php
declare(strict_types=1);

namespace ShoppinPal\YapepCommon\Controller;

class PhpInput
{
    public function getStdIn(): string
    {
        return file_get_contents('php://input');
    }
}
