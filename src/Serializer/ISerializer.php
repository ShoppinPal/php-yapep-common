<?php

namespace ShoppinPal\YapepCommon\Serializer;

use YapepBase\Exception\ParameterException;

interface ISerializer
{

    /**
     * Serialize the specified data into a string
     *
     * @param mixed $data
     *
     * @return string
     *
     * @throws ParameterException If the specified data cannot be serialized by this serializer.
     */
    public function serialize($data);

    /**
     * Unserialize the specified string into the original value (or something that would serialize to the same string)
     *
     * @param string $data
     *
     * @return mixed
     *
     * @throws ParameterException If the data can not be unserialized (ie. bad string format).
     */
    public function unserialize($data);
}
