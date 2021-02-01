<?php

namespace ShoppinPal\YapepCommon\Robo\Swagger;

use ShoppinPal\YapepCommon\Robo\ApplicationConfig;

class OpenApiGenerator implements GeneratorInterface
{
    public function addErrorsToSwaggerJsonContent(array &$jsonContent, ApplicationConfig $config): void
    {
        $usedErrorCodes = [];

        foreach ($jsonContent['paths'] ?? [] as $path => $methods) {
            foreach ($methods as $method => $methodContent) {
                $errorCodes = array_merge(
                    $config->getErrorCodes($method),
                    $methodContent['x-errors'] ?? []
                );

                foreach ($errorCodes as $errorCode) {
                    $usedErrorCodes[$errorCode] = 1;

                    // This status code is already documented, don't override
                    if (isset($methodContent['responses'][(string)$errorCode])) {
                        continue;
                    }

                    $methodContent['responses'][(string)$errorCode] = [
                        'description' => ErrorHelper::getApiErrorDocDescriptionByErrorCode($errorCode),
                        'content'     => [
                            'application/json' => [
                                'schema' => [
                                    '$ref' => '#/components/schemas/Error' . $errorCode,
                                ],
                            ]
                        ]
                    ];
                }
                if (isset($methodContent['x-errors'])) {
                    unset($methodContent['x-errors']);
                }

                $jsonContent['paths'][$path][$method] = $methodContent;
            }
        }

        foreach ($usedErrorCodes as $errorCode => $value) {
            if (!isset($jsonContent['components']['schemas']['Error' . $errorCode])) {
                $jsonContent['components']['schemas']['Error' . $errorCode] = ErrorHelper::getApiDocErrorResponseDefinitionForCode(
                    $errorCode
                );
            }
        }
    }

}