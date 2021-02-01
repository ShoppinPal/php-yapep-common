<?php

namespace ShoppinPal\YapepCommon\Robo\Swagger;

use ShoppinPal\YapepCommon\Robo\ApplicationConfig;

interface GeneratorInterface
{
    public function addErrorsToSwaggerJsonContent(array &$jsonContent, ApplicationConfig $config): void;
}