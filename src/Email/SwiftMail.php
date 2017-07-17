<?php

namespace ShoppinPal\YapepCommon\Email;

use YapepBase\Config;

class SwiftMail implements IEmail
{
    /** @var \Swift_SmtpTransport */
    protected $transport;

    /** @var string */
    protected $senderEmail;

    public function __construct($configName)
    {
        $config = Config::getInstance();

        $this->senderEmail = $config->get('commonResource.email.' . $configName . '.senderEmail');

        $this->transport = new \Swift_SmtpTransport(
            $config->get('commonResource.swiftMailer.' . $configName . 'smtpHost'),
            $config->get('commonResource.swiftMailer.' . $configName . 'smtpPort', 25)
        );

        $username = $config->get('commonResource.swiftMailer.' . $configName . 'smtpUsername', '');
        $password = $config->get('commonResource.swiftMailer.' . $configName . 'smtpPassword', '');

        if ($username && $password) {
            $this->transport->setUsername($username)
                ->setPassword($password);
        }
    }

    public function sendEmail(
        $to,
        $subject,
        $htmlBody = null,
        $textBody = null,
        array $ccAddresses = [],
        $bccAddresses = [],
        $charset = 'UTF-8'
    ) {
        $message = new \Swift_Message($subject);

        if ($textBody) {
            $message->setBody($textBody, null, $charset);
            if ($htmlBody) {
                $message->addPart($htmlBody, 'text/html', $charset);
            }
        } else {
            $message->setBody($htmlBody, 'text/html', $charset);
        }

        $message->setFrom($this->senderEmail)
            ->setTo($to);

        foreach ($ccAddresses as $ccAddress) {
            $message->addCc($ccAddress);
        }

        foreach ($bccAddresses as $bccAddress) {
            $message->addCc($bccAddress);
        }

        $mailer = new \Swift_Mailer($this->transport);
        return $mailer->send($message);
    }

}
