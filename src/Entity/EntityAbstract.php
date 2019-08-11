<?php
declare(strict_types = 1);

namespace ShoppinPal\YapepCommon\Entity;

use ArrayAccess;
use ArrayIterator;
use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use IteratorAggregate;
use YapepBase\DataObject\NotSetValue;
use YapepBase\Exception\ParameterException;

abstract class EntityAbstract implements IteratorAggregate, ArrayAccess
{
    public function __construct($entityData = [])
    {
        $this->populateFromArray($entityData);

        if (!empty($this->id) && !is_null($this->id) && is_numeric($this->id)) {
            $this->id = (int)$this->id;
        }
    }

    protected function populateFromArray(array $entityData): void
    {
        if (empty($entityData)) {
            return;
        }

        foreach (get_object_vars($this) as $attribute => $value) {
            $this->$attribute = $this->getFromArray($entityData, $attribute);
        }
    }

    protected function getFromArray(array $array, $key)
    {
        if (!array_key_exists($key, $array)) {
            throw new ParameterException('Key "' . $key . '" does not exist in given array!');
        }

        return $array[$key];
    }

    public function offsetExists($offset): bool
    {
        return isset($this->$offset);
    }

    public function offsetGet($offset)
    {
        return $this->$offset;
    }

    public function offsetSet($offset, $value): void
    {
        $this->$offset = $value;
    }

    public function offsetUnset($offset): void
    {
        unset($this->$offset);
    }

    public function getIterator(): ArrayIterator
    {
        return new ArrayIterator($this->toArray());
    }

    public function replaceNotSetValues($newValue = null)
    {
        $newEntity = clone $this;

        foreach (get_object_vars($newEntity) as $attribute => $value) {
            if ($value instanceof NotSetValue) {
                $newEntity->$attribute = $newValue;
            }
        }

        return $newEntity;
    }

    protected function getCarbonFromDateTimeString(string $dateString): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d H:i:s', $dateString, new \DateTimeZone('UTC'));
    }

    protected function getCarbonFromNullableDateTimeString(?string $dateString): ?CarbonImmutable
    {
        return $dateString ? $this->getCarbonFromDateTimeString($dateString) : null;
    }

    protected function getCarbonFromDateString(string $dateString): CarbonImmutable
    {
        return CarbonImmutable::createFromFormat('Y-m-d', $dateString, new \DateTimeZone('UTC'));
    }

    protected function getCarbonFromNullableDateString(?string $dateString): ?CarbonImmutable
    {
        return $dateString ? $this->getCarbonFromDateString($dateString) : null;
    }

    protected function getDateTimeStringFromCarbon(CarbonInterface $carbon): string
    {
        return $carbon->setTimezone(new \DateTimeZone('UTC'))->toDateTimeString();
    }

    protected function getDateTimeStringFromNullableCarbon(?CarbonInterface $carbon): ?string
    {
        return null === $carbon ? null : $this->getDateTimeStringFromCarbon($carbon);
    }

    protected function getDateStringFromCarbon(CarbonInterface $carbon): string
    {
        return $carbon->setTimezone(new \DateTimeZone('UTC'))->toDateString();
    }

    protected function getDateStringFromNullableCarbon(?Carbon $carbon): ?string
    {
        return null === $carbon ? null : $this->getDateStringFromCarbon($carbon);
    }

    public function toArray(): array
    {
        $result = [];

        foreach (get_object_vars($this) as $attribute => $value) {
            $result[$attribute] = $value;
        }

        return $result;
    }
}
