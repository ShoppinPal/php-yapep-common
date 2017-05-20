<?php
declare(strict_types=1);

namespace ShoppinPal\YapepCommon\DataObject;

use Carbon\Carbon;

abstract class ApiDoAbstract
{
    private static $classPropertyCache = [];

    protected $ignoredProperties = [];

    /**
     * Encodes the object as a JSON string
     *
     * @return string
     */
    public function toJson(): string
    {
        return json_encode($this->getSerializableContents());
    }

    /**
     * Returns the contents of the object as an associative array, with all complex values replaced by primitives.
     *
     * @return array
     */
    public function getSerializableContents(): array
    {
        $className = get_class($this);

        if (!isset(static::$classPropertyCache[$className])) {
            $objectVars = array_keys(get_object_vars($this));
            static::$classPropertyCache[$className] = array_diff(
                $objectVars,
                array_merge($this->ignoredProperties, ['ignoredProperties'])
            );
        }

        $results = [];

        foreach (static::$classPropertyCache[$className] as $property) {
            $results[$property] = $this->getSimpleValue($this->$property);
        }

        return $results;
    }

    /**
     * Returns the contents of an array with all complex values replaced by primitives.
     *
     * @param array $array
     *
     * @return array
     */
    public static function getArrayContentAsSerializableArray(array $array): array
    {
        return static::getSimpleValue($array);
    }

    /**
     * @param mixed $value
     *
     * @return mixed
     */
    private static function getSimpleValue($value)
    {
        if ($value instanceof ApiDoAbstract) {
            return $value->getContents();
        } elseif ($value instanceof Carbon) {
            return $value->toIso8601String();
        } elseif ($value instanceof \DateTime) {
            return $value->format(DATE_ATOM);
        } elseif (is_object($value)) {
            if (method_exists($value, '__toString')) {
                return $value->__toString();
            } elseif (method_exists($value, 'toString')) {
                return $value->toString();
            }
        } elseif (is_array($value)) {
            $result = [];
            foreach ($value as $key => $arrayValue) {
                $result[$key] = static::getSimpleValue($arrayValue);
            }
            return $result;
        } else {
            return $value;
        }

    }
}
