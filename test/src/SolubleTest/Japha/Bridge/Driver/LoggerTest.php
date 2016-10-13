<?php

namespace SolubleTest\Japha\Bridge;

use Soluble\Japha\Bridge\Adapter;
use Soluble\Japha\Bridge\Driver\Pjb62\PjbProxyClient;
use Monolog\Logger;
use Monolog\Handler\TestHandler;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2014-11-04 at 16:47:42.
 */
class LoggerTest extends \PHPUnit_Framework_TestCase
{
    /**
     *
     * @var string
     */
    protected $servlet_address;

    /**
     *
     * @var Adapter
     */
    protected $adapter;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @var TestHandler
     */
    protected $loggerTestHandler;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        \SolubleTestFactories::startJavaBridgeServer();
        $this->servlet_address = \SolubleTestFactories::getJavaBridgeServerAddress();
        $this->logger = new Logger('test');
        $this->loggerTestHandler = new TestHandler(Logger::DEBUG);
        $this->logger->pushHandler($this->loggerTestHandler);
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testServerDownLogConnectionException()
    {
        PjbProxyClient::unregisterInstance();
        $logged = false;
        try {
            $ba = new Adapter([
                'driver' => 'pjb62',
                //'servlet_address' => $this->servlet_address . 'urldoesnotexists'
                'servlet_address' => 'http://127.0.0.1:12345/servlet.phpjavabridge'
            ], $this->logger);
        } catch (\Soluble\Japha\Bridge\Exception\ConnectionException $e) {
            $a = $this->loggerTestHandler;
            $mustContain = '[soluble-japha] Cannot connect to php-java-bridge server';
            $logged = $this->loggerTestHandler->hasCriticalThatContains($mustContain);

            $this->assertTrue($logged, 'Assert that logger actually logs connection exception');
        } catch (\Exception $e) {
            $this->assertFalse(true, "ConnectionException should be thrown !!!");
        }
        if (!$logged) {
            $this->assertFalse(true, "ConnectionException should be logged");
        }
    }
}