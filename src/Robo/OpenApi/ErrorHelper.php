<?php

namespace ShoppinPal\YapepCommon\Robo\OpenApi;

class ErrorHelper
{
    public static function getApiErrorDocDescriptionByErrorCode(int $errorCode): string
    {
        switch ((int)$errorCode) {
            case 400:
                return 'Bad request, the request parameters are invalid';

            case 401:
                return 'Authorization required for calling this endpoint';

            case 402:
                return 'Billing error, payment required';

            case 403:
                return 'The authenticated user has no permission for this operation';

            case 404:
                return 'Entity not found';

            default:
                return 'Error';
        }
    }

    public static function getApiDocErrorResponseDefinitionForCode(int $errorCode): array
    {
        $errorDefinition = [
            'properties' => [
                'errorCode'    => [
                    'type'        => 'string',
                    'description' => 'The code of the error'
                ],
                'errorMessage' => [
                    'type'        => 'string',
                    'description' => 'Description of the error',
                ]
            ],
            'type' => 'object'
        ];

        if (400 == $errorCode) {
            $errorDefinition['properties']['params'] = [
                'type'        => 'object',
                'description' => 'List of the invalid params where the property is the parameter name and the value is the describing the issue'
            ];
        }

        return $errorDefinition;
    }
}
