<?php

namespace ShoppinPal\YapepCommon\Email;

use YapepBase\Exception\ParameterException;

interface IEmail
{
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
    public function sendEmail(
        $to,
        $subject,
        $htmlBody = null,
        $textBody = null,
        array $ccAddresses = [],
        $bccAddresses = [],
        $charset = 'UTF-8'
    );
}
