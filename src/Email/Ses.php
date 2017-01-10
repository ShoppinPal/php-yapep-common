<?php

namespace ShoppinPal\YapepCommon\Email;

use Aws\Ses\SesClient;
use YapepBase\Config;
use YapepBase\Exception\ParameterException;

class Ses
{

    protected $sesClient;

    protected $senderEmail;

    /**
     * @param string $configName
     */
    public function __construct($configName)
    {
        $config       = Config::getInstance();
        $region       = $config->get('commonResource.ses.' . $configName . '.region', '');
        $clientConfig = [
            'version'     => $config->get('commonResource.ses.' . $configName . '.version', 'latest'),
            'credentials' => [
                'key'    => $config->get('commonResource.ses.' . $configName . '.accessKeyId'),
                'secret' => $config->get('commonResource.ses.' . $configName . '.accessSecret'),
            ],
        ];

        $this->senderEmail = $config->get('commonResource.ses.' . $configName . '.senderEmail');

        if ($region) {
            $clientConfig['region'] = $region;
        }

        $this->sesClient = new SesClient($clientConfig);
    }

    /**
     * @param string $to
     * @param string $subject
     * @param string $htmlBody
     * @param string $textBody
     * @param array  $ccAddresses
     * @param array  $bccAddresses
     * @param string $charset
     *
     * @return mixed|null
     * @throws ParameterException
     */
    public function sendEmail($to, $subject, $htmlBody = null, $textBody = null, array $ccAddresses = [], $bccAddresses = [], $charset = 'UTF-8')
    {
        if (empty($htmlBody) && empty($textBody)) {
            throw new ParameterException('Either the HTML or the text body must be set');
        }

        $message = [
            'Subject' => [
                'Charset' => $charset,
                'Data'    => $subject,
            ],
            'Body' => [],
        ];

        if ($htmlBody) {
            $message['Body']['Html'] = [
                'Charset' => $charset,
                'Data'    => $htmlBody,
            ];
        }

        if ($textBody) {
            $message['Body']['Text'] = [
                'Charset' => $charset,
                'Data'    => $textBody,
            ];
        }

        $args = [
            'Destination' => [
                'CcAddresses' => $ccAddresses,
                'BccAddresses' => $bccAddresses,
                'ToAddresses' => (array)$to,
            ],
            'Message' => $message,
            'Source'  => $this->senderEmail,
        ];

        $result = $this->sesClient->sendEmail($args);

        return $result->get('MessageId');
    }
}
