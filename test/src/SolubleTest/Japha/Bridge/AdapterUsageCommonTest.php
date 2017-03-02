<?php

namespace SolubleTest\Japha\Bridge;

use Soluble\Japha\Bridge\Adapter;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-11-04 at 16:47:42.
 */
class AdapterUsageCommonTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $servlet_address;

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
        \SolubleTestFactories::startJavaBridgeServer();

        $this->servlet_address = \SolubleTestFactories::getJavaBridgeServerAddress();

        $this->adapter = new Adapter([
            'driver' => 'Pjb62',
            'servlet_address' => $this->servlet_address,
        ]);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testMethodCall()
    {
        $ba = $this->adapter;

        $javaString = $ba->java('java.lang.String', 'A key is a key!');
        $index = $javaString->indexOf('key');
        $this->assertEquals(2, $index);

        // Method overloading, use the `indexOf(String, $fromIndex)` method
        $index = $javaString->indexOf('key', $fromIndex = 8);
        $this->assertEquals(11, $index);
    }

    public function testJavaBigInt()
    {
        $ba = $this->adapter;
        $bigint1 = $ba->java('java.math.BigInteger', 10);
        $bigint2 = $ba->java('java.math.BigInteger', 1234567890);
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $bigint1);
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $bigint2);
        $bigint1 = $bigint1->add($bigint2);

        $this->assertEquals('1234567900', (string) $bigint1);
        $this->assertEquals(1234567900, $bigint1->intValue());
    }

    public function testOperators()
    {
        $ba = $this->adapter;
        $bigint = $ba->java('java.math.BigInteger', 10);
        $a = $bigint->intValue() * 4;
        $this->assertEquals(40, $a);

        $int = $ba->java('java.lang.Integer', 10);
        $a = $int->intValue() * 4.15;
        $this->assertEquals(41.5, $a);
    }

    public function testJavaStrings()
    {
        $ba = $this->adapter;

        // ascii
        $string = $ba->java('java.lang.String', 'Am I the only one ?');
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $string);
        $this->assertEquals('Am I the only one ?', $string);
        $this->assertNotEquals('Am I the only one', $string);

        // unicode - utf8
        $string = $ba->java('java.lang.String', '保障球迷權益');
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $string);
        $this->assertEquals('保障球迷權益', $string);
        $this->assertNotEquals('保障球迷', $string);

        $string = $ba->java('java.lang.String', '保éà');
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $string);
        $this->assertEquals('保éà', $string);
    }

    public function testJavaHashMap()
    {
        $ba = $this->adapter;
        $hash = $ba->java('java.util.HashMap', ['my_key' => 'my_value']);
        $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $hash);
        $this->assertEquals('my_value', $hash->get('my_key'));
        $hash->put('new_key', 'oooo');
        $this->assertEquals('oooo', $hash->get('new_key'));
        $hash->put('new_key', 'pppp');
        $this->assertEquals('pppp', $hash->get('new_key'));

        $this->assertEquals(4, $hash->get('new_key')->length());

        $hash->put('key', $ba->java('java.lang.String', '保障球迷權益'));
        $this->assertEquals('保障球迷權益', $hash->get('key'));
        $this->assertEquals(6, $hash->get('key')->length());

        $hashMap = $ba->java('java.util.HashMap', [
            'year' => 2017,
            'message' => 'Hello world',
            'data' => [0.2, 3.2, 4, 18.12]
        ]);

        $hashMap->put('message', '你好，世界');
        $this->assertEquals('你好，世界', $hashMap->get('message'));
        //echo $hashMap->get('year') + 1;
    }

    public function testJavaHashMapArrayValues()
    {
        $ba = $this->adapter;
        $array = [
            'name' => 'John doe',
            'age' => 26
        ];
        $map = $ba->java('java.util.HashMap', $array);

        $values = $map->values()->toArray();

        $v = $ba->values($values);
        $this->assertEquals(array_values($array), $v);
    }

    public function testJavaReflectArray()
    {
        $ba = $this->adapter;
        $arrayClass = $ba->javaClass('java.lang.reflect.Array');

        $array = [
            'name' => 'John doe',
            'age' => 26
        ];
        $map = $ba->java('java.util.HashMap', $array);

        $values = $map->values()->toArray();

        $v = [];
        for ($i = 0; $i < $arrayClass->getLength($values); ++$i) {
            $v[$i] = (string) $values[$i];
        }
        $this->assertEquals(array_values($array), $v);
    }

    public function testJavaConstructorOverloading()
    {
        $ba = $this->adapter;
        $vec1 = $ba->java('java.util.Vector', $initialCapacity = 1, $capacityIncrement = 2);
        $this->assertEquals('java.util.Vector', $ba->getDriver()->getClassName($vec1));
        $vec2 = $ba->java('java.util.Vector', [1, 2, 3]);
        $this->assertEquals('java.util.Vector', $ba->getDriver()->getClassName($vec2));

        $mathContext = $ba->java('java.math.MathContext', $precision = 2);
        $bigint = $ba->java('java.math.BigInteger', 123456);
        $bigdec = $ba->java('java.math.BigDecimal', $bigint, $scale = 2, $mathContext);

        $this->assertEquals(1200, $bigdec->floatValue());
    }

    public function testFileReader()
    {
        $ba = $this->adapter;
        $reader = $ba->java('java.io.BufferedReader',
            $ba->java('java.io.FileReader', __FILE__)
        );

        $content = '';
        while (($line = $reader->readLine()) != null) {
            $content .= (string) $line . PHP_EOL;
        }
        $this->assertContains('testFileReader', $content);
    }

    public function testForeach()
    {
        $ba = $this->adapter;

        $arr = [
            'key1' => 'first_value',
            'key2' => 'second_value',
            'key3' => ['cool', 'cool', 'cool']
        ];

        $hashMap = $ba->java('java.util.HashMap', $arr);

        $newArr = [];
        foreach ($hashMap as $key => $value) {
            $newArr[$key] = $ba->getDriver()->values($value);
        }
        $this->assertEquals($arr, $newArr);
    }

    public function testIterator()
    {
        $ba = $this->adapter;

        $system = $ba->javaClass('java.lang.System');
        $properties = $system->getProperties();

        foreach ($properties as $key => $value) {
            $this->assertInternalType('string', $key);
            $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $value);

            if ($key == 'java.version') {
                $this->assertStringStartsWith('1', $value->__toString());
            }
        }

        $iterator = $properties->getIterator();
        $this->assertInstanceOf('Soluble\Japha\Bridge\Driver\Pjb62\ObjectIterator', $iterator);
        $this->assertInstanceOf('Iterator', $iterator);

        foreach ($iterator as $key => $value) {
            $this->assertInternalType('string', $key);
            $this->assertInstanceOf('Soluble\Japha\Interfaces\JavaObject', $value);

            if ($key == 'java.version') {
                $this->assertStringStartsWith('1', $value->__toString());
            }
        }
    }
}
