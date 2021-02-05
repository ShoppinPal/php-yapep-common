<?php

namespace ShoppinPal\YapepCommon\Robo\OpenApi;

use ShoppinPal\YapepCommon\Robo\ApplicationConfig;

interface GeneratorInterface
{
    public function addErrorsToOpenApiJsonContent(array &$jsonContent, ApplicationConfig $config): void;
}
