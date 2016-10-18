<?php

namespace ShoppinPal\YapepCommon\Serializer;

use YapepBase\Exception\ParameterException;

class SerializeSerializer implements ISerializer
{

    /**
     * @inheritdoc
     */
    public function serialize($data)
    {
        return serialize($data);
    }

    /**
     * @inheritdoc
     */
    public function unserialize($data)
    {
        $result = unserialize($data);

        if (false === $result && 'b:0;' != strtolower(trim($data))) {
            throw new ParameterException(
                'Unable to unserialzie the string "' . substr($data, 0, 100) . '"',
                0,
                null,
                ['originalString' => $data]
            );
        }

        return $result;
    }

}
