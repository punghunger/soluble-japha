<?php

/*
 * Soluble Japha
 *
 * @link      https://github.com/belgattitude/soluble-japha
 * @copyright Copyright (c) 2013-2017 Vanvelthem Sébastien
 * @license   MIT License https://github.com/belgattitude/soluble-japha/blob/master/LICENSE.md
 */

namespace SolubleTest\Japha\Bridge\Driver\Pjb62;

use Soluble\Japha\Bridge\Driver\Pjb62\Exception\InternalException;
use Soluble\Japha\Bridge\Driver\Pjb62\ParserFactory;
use Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient;
use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Driver\Pjb62\Java;
use Soluble\Japha\Bridge\Exception\BrokenConnectionException;
use Soluble\Japha\Bridge\Exception\InvalidArgumentException;
use Soluble\Japha\Bridge\Exception\InvalidUsageException;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2015-11-13 at 10:21:03.
 */
class PjbProxyClientTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $servlet_address;

    /**
     * @var array
     */
    protected $options;

    /**
     * @var Adapter
     */
    protected $adapter;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->servlet_address = \SolubleTestFactories::getJavaBridgeServerAddress();
        $this->options = [
            'servlet_address' => $this->servlet_address,
            'java_prefer_values' => true,
        ];
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        $this->clearPjbProxyClientSingleton();
    }

    public function testGetInstance()
    {
        $pjbProxyClient = PjbProxyClient::getInstance($this->options);

        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient', $pjbProxyClient);
        $this->assertTrue(PjbProxyClient::isInitialized());
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\Client', $pjbProxyClient->getClient());

        $pjbProxyClient->unregisterInstance();
        $this->assertFalse(PjbProxyClient::isInitialized());
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient', $pjbProxyClient);
    }

    public function testGetInstanceThrowsInvalidUsageException()
    {
        $this->expectException(InvalidUsageException::class);
        $pjbProxyClient = PjbProxyClient::getInstance($this->options);

        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient', $pjbProxyClient);
        $this->assertTrue(PjbProxyClient::isInitialized());
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\Client', $pjbProxyClient->getClient());

        $pjbProxyClient->unregisterInstance();
        $this->assertFalse(PjbProxyClient::isInitialized());

        PjbProxyClient::getInstance();
    }

    public function testGetClientThrowsBrokenConnectionException()
    {
        $this->expectException(BrokenConnectionException::class);
        $pjbProxyClient = PjbProxyClient::getInstance($this->options);

        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient', $pjbProxyClient);
        $this->assertTrue(PjbProxyClient::isInitialized());
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\Client', $pjbProxyClient->getClient());

        $pjbProxyClient->unregisterInstance();
        $this->assertFalse(PjbProxyClient::isInitialized());

        PjbProxyClient::getClient();
    }

    public function testGetJavaClass()
    {
        $pjbProxyClient = PjbProxyClient::getInstance($this->options);
        $cls = $pjbProxyClient->getJavaClass('java.lang.Class');
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaClass', $cls);
    }

    public function testInvokeMethod()
    {
        $pjbProxyClient = PjbProxyClient::getInstance($this->options);
        $bigint1 = new Java('java.math.BigInteger', 10);
        $value = $pjbProxyClient->invokeMethod($bigint1, 'intValue');
        $this->assertEquals(10, $value);

        $bigint2 = new Java('java.math.BigInteger', 20);
        $bigint3 = $pjbProxyClient->invokeMethod($bigint1, 'add', [$bigint2]);
        $this->assertEquals(30, $bigint3->intValue());
    }

    public function testGetClearLastException()
    {
        $pjbProxyClient = PjbProxyClient::getInstance($this->options);

        try {
            $pjbProxyClient->getJavaClass('ThisClassWillThrowException');
        } catch (\Exception $e1) {
            // Do nothing
        }

        $e = $pjbProxyClient->getLastException();
        $this->assertInstanceOf(InternalException::class, $e);
        $pjbProxyClient->clearLastException();

        $e = $pjbProxyClient->getLastException();
        $this->assertNull($e);
    }

    public function testGetOptions()
    {
        $options = PjbProxyClient::getInstance($this->options)->getOptions();
        $this->assertEquals($this->options['servlet_address'], $options['servlet_address']);
    }

    public function testGetCompatibilityOption()
    {
        $option = PjbProxyClient::getInstance($this->options)->getCompatibilityOption();
        $this->assertEquals('B', $option);
    }

    public function testGetOptionThrowsException()
    {
        $this->setExpectedException(InvalidArgumentException::class);
        PjbProxyClient::getInstance($this->options)->getOption('DOESNOTEXISTS');
    }

    public function testForceSimpleParser()
    {
        // Should create a NativeParser by default
        $defaultClient = PjbProxyClient::getInstance($this->options)::getClient();
        $this->assertEquals($defaultClient->RUNTIME['PARSER'], ParserFactory::PARSER_NATIVE);

        // Recreate singleton, this time forcing the simple parser
        $this->clearPjbProxyClientSingleton();

        $proxyClient = PjbProxyClient::getInstance(array_merge(
            $this->options,
            [
                'force_simple_xml_parser' => true
            ]
        ));
        $client = $proxyClient::getClient();
        $this->assertEquals($client->RUNTIME['PARSER'], ParserFactory::PARSER_SIMPLE);

        // Test protocol
        $cls = $proxyClient->getJavaClass('java.lang.Class');
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaClass', $cls);

        $str = new Java('java.lang.String', 'Hello');
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $str);
        $len = $str->length();
        $this->assertEquals(5, $len);

        // Clean up client instance
        $this->clearPjbProxyClientSingleton();
    }

    /**
     * Clears the protected static variables of PjbProxyClient to force reinitialization.
     */
    protected function clearPjbProxyClientSingleton()
    {
        PjbProxyClient::unregisterInstance();
        /*
        $refl = new \ReflectionClass(PjbProxyClient::class);
        $propertiesToClear = [
            'instance',
            'instanceOptionsKey',
            'client'
        ];

        foreach ($propertiesToClear as $propertyName) {
            $reflProperty = $refl->getProperty($propertyName);
            $reflProperty->setAccessible(true);
            $reflProperty->setValue(null, null);
            $reflProperty->setAccessible(false);
        }
        */
    }

    public function testOverrideDefaultOptions()
    {
        $defaultOptions = (array) PjbProxyClient::getInstance($this->options)->getOptions();

        // For sake of simplicity just inverse the boolean default options
        $overriddenOptions = $defaultOptions;
        foreach ($overriddenOptions as $option => $value) {
            if (is_bool($value)) {
                $overriddenOptions[$option] = !$value;
            } else {
                $overriddenOptions[$option] = $value;
            }
        }

        // Clear previous singleton to force re-creation of the object
        $this->clearPjbProxyClientSingleton();

        $options = (array) PjbProxyClient::getInstance($overriddenOptions)->getOptions();

        foreach ($options as $option => $value) {
            if (is_bool($value)) {
                $this->assertNotEquals($value, $defaultOptions[$option]);
            }
        }
    }
}
