<?php
namespace FluidTYPO3\Development;

/*
 * This file is part of the fluidtypo3/development project under GPLv2 or later.
 *
 * For the full copyright and license information, please read the
 * LICENSE.md file that was distributed with this source code.
 */

use PHPUnit\Framework\TestCase;
use TYPO3\CMS\Core\Authentication\BackendUserAuthentication;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Object\ObjectManagerInterface;

/**
 * AbstractTestCase
 */
abstract class AbstractTestCase extends TestCase
{
    /**
     * Helper function to call protected or private methods
     *
     * @param object $object The object to be invoked
     * @param string $name the name of the method to call
     * @param mixed $arguments
     * @return mixed
     */
    protected function callInaccessibleMethod($object, $name, ...$arguments)
    {
        $reflectionObject = new \ReflectionObject($object);
        $reflectionMethod = $reflectionObject->getMethod($name);
        $reflectionMethod->setAccessible(true);
        return $reflectionMethod->invokeArgs($object, $arguments);
    }

    /**
     * @param string $propertyName
     * @param mixed $value
     * @param mixed $expectedValue
     * @param mixed $expectsChaining
     * @return void
     */
    protected function assertGetterAndSetterWorks($propertyName, $value, $expectedValue = null, $expectsChaining = false)
    {
        $instance = $this->createInstance();
        $setter = 'set' . ucfirst($propertyName);
        $getter = 'get' . ucfirst($propertyName);
        $chained = $instance->$setter($value);
        if (true === $expectsChaining) {
            $this->assertSame($instance, $chained);
        } else {
            $this->assertNull($chained);
        }
        $this->assertEquals($expectedValue, $instance->$getter());
    }

    /**
     * @return object
     */
    protected function createInstanceClassName()
    {
        return str_replace('Tests\\Unit\\', '', substr(get_class($this), 0, -4));
    }

    /**
     * @return object
     */
    protected function createInstance()
    {
        $instance = $this->objectManager->get($this->createInstanceClassName());
        return $instance;
    }

    public static function assertAttributeSame($expected, string $attribute, object $object, $message = ''): void
    {
        self::assertSame($expected, ProtectedAccess::getProperty($object, $attribute), $message);
    }

    public static function assertAttributeEquals($expected, string $attribute, object $object, $message = ''): void
    {
        self::assertEquals($expected, ProtectedAccess::getProperty($object, $attribute), $message);
    }

    public static function assertAttributeInstanceOf($expectedClass, string $attribute, object $object, $message = ''): void
    {
        self::assertInstanceOf($expectedClass, ProtectedAccess::getProperty($object, $attribute), $message);
    }
}
