<?php
namespace FluidTYPO3\Development;

/*
 * This file is part of the FluidTYPO3/Flux project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

class ProtectedAccess
{
    public static function setProperty($subject, string $propertyName, $value): void
    {
        if (!property_exists($subject, $propertyName)) {
            $subject->$propertyName = $value;
        } else {
            $reflectionProperty = new \ReflectionProperty($subject, $propertyName);
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($subject, $value);
        }
    }

    public static function getProperty($subject, string $propertyName)
    {
        $reflectionProperty = new \ReflectionProperty($subject, $propertyName);
        $reflectionProperty->setAccessible(true);
        return $reflectionProperty->getValue($subject);
    }
}
