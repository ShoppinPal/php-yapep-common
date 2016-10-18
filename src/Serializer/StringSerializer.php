<?php

namespace ShoppinPal\YapepCommon\Serializer;

/**
 * A serializer that will simply cast the passed data to string.
 *
 * WARNING, this will likely mean losing data, and the unserialization doesn't do anything, as it's unable to recover
 * the original type.
 */
class StringSerializer implements ISerializer
{

    /**
     * @inheritdoc
     */
    public function serialize($data)
    {
        return (string)$data;
    }

    /**
     * @inheritdoc
     */
    public function unserialize($data)
    {
        // Nothing to do, unable to reverse casting to string
        return $data;
    }
}
