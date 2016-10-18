<?php

namespace ShoppinPal\YapepCommon\Serializer;

use YapepBase\Exception\ParameterException;

/**
 * Class for serializing almost
 */
class JsonSerializer implements ISerializer
{

    /**
     * @inheritdoc
     */
    public function serialize($data)
    {
        $result = json_encode($data);

        if (false === $result) {
            throw new ParameterException(
                'Unable to json_encode the specified data for serialization. Error: ' . json_last_error_msg()
            );
        }

        return $result;
    }

    /**
     * @inheritdoc
     */
    public function unserialize($data)
    {
        $result = json_decode($data, true);

        if (null === $result && 'null' != strtolower(trim($data))) {
            throw new ParameterException(
                'Unable to unserialzie the string. JSON error: "' . json_last_error_msg() . '" "'
                    . substr($data, 0, 100) . '"',
                0,
                null,
                ['originalString' => $data]
            );
        }

        return $result;
    }

}
