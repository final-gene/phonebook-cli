<?php declare(strict_types=1);
/**
 * This file is part of the PhoneBook CLI project.
 *
 * @author Frank Giesecke <frank.giesecke@vivamera.com>
 */

namespace FinalGene\PhoneBook\Utils;

use ReflectionMethod;
use ReflectionProperty;

/**
 * Test helper trait.
 *
 * @package Vivamera\Utils
 */
trait TestHelperTrait
{
    /**
     * Get method by reflection.
     *
     * @param string $class  Class name
     * @param string $method Method name
     *
     * @return ReflectionMethod
     * @throws \ReflectionException
     */
    public static function getMethod(string $class, string $method): ReflectionMethod
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $reflectionMethod = new ReflectionMethod($class, $method);
        $reflectionMethod->setAccessible(true);

        return $reflectionMethod;
    }

    /** @noinspection PhpUndefinedClassInspection */
    /**
     * Invoke method by reflection.
     *
     * @param object $object Target object
     * @param string $method Method name
     * @param array  $args   Argument list fpr invocation
     *
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    public static function invokeMethod($object, string $method, array $args = [])
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $class = self::getClassName($object);
        $reflectionMethod = self::getMethod($class, $method);

        return $reflectionMethod->invokeArgs($object, $args);
    }

    /**
     * Get property by reflection.
     *
     * @param string $class    Class name
     * @param string $property Property name
     *
     * @return ReflectionProperty
     * @throws \ReflectionException
     */
    public static function getProperty(string $class, string $property): ReflectionProperty
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $reflectionProperty = new ReflectionProperty($class, $property);
        $reflectionProperty->setAccessible(true);

        return $reflectionProperty;
    }

    /** @noinspection PhpUndefinedClassInspection */
    /**
     * Get property value.
     *
     * @param object $object   Target object
     * @param string $property Property name
     *
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    public static function getPropertyValue($object, string $property)
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $class = static::getClassName($object);
        $reflectionProperty = self::getProperty($class, $property);

        return $reflectionProperty->getValue($object);
    }

    /** @noinspection PhpUndefinedClassInspection */
    /**
     * Set property value by reflection.
     *
     * @param object $object   Target object
     * @param string $property Property name
     * @param mixed  $value    Property value
     *
     * @return void
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    public static function setPropertyValue($object, string $property, $value): void
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $class = self::getClassName($object);
        $reflectionProperty = self::getProperty($class, $property);
        $reflectionProperty->setValue($object, $value);
    }

    /** @noinspection PhpUndefinedClassInspection */
    /**
     * Set property values
     *
     * @param object  $object      Target object
     * @param mixed[] $propertyMap Property with property name as key and property value as value
     *
     * @return void
     * @throws \InvalidArgumentException
     * @throws \ReflectionException
     */
    public static function setPropertyValues($object, array $propertyMap): void
    {
        /** @noinspection ExceptionsAnnotatingAndHandlingInspection */
        $class = self::getClassName($object);
        foreach ($propertyMap as $property => $value) {
            $reflectionProperty = self::getProperty($class, $property);
            $reflectionProperty->setValue($object, $value);
        }
    }

    /** @noinspection PhpUndefinedClassInspection */
    /**
     * Get class name
     *
     * @param object $object Source object
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    private static function getClassName(object $object): string
    {
        $class = \get_class($object);
        if (false === strpos($class, 'Mock_')) {
            return $class;
        }

        $class = \get_parent_class($object);

        if (false === $class) {
            throw new \InvalidArgumentException('Unable to get class from provided object.');
        }

        return $class;
    }
}
