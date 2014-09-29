<?php
use uLogger\Logger as log;
require_once "src/uLogger.php";
require_once "src/writers/TestWriter.php";


class uLoggerTest extends PHPUnit_Framework_TestCase {

    protected $writer;
    protected $log;

    public function setUp() {
        $this->writer = $this->getMock('uLogger\\TestWriter', array('write'));
        $this->log = new log(log::DEBUG);
        $this->log->attachWriter($this->writer);
    }

    public function testMsgLogLevelTooLow() {
        $log = new log(log::ERROR);
        $this->assertFalse($log->info("should return false"));
    }

    public function testMsgLogging() {
        $this->writer->expects($this->once())->method("write");
        $result = $this->log->error("should get logged");
        $this->assertEquals("should get logged", $result["message"]);
        $this->assertEquals("ERROR", $result["level_name"]);
        $this->assertInternalType("string", $result["datetime"]);
    }

    public function testLogInteger() {
        $result = $this->log->debug(1);
        $this->assertEquals("1", $result['message']);
    }

    public function testLogInterpolation() {
        $result = $this->log->debug("Hello {test}!", array("test" => "World"));
        $this->assertEquals("Hello World!", $result['message']);
    }

    public function testBadMsgTypes() {
        $result = $this->log->debug(array());
        $this->assertEquals(null, $result['message']);
    }

    public function testMsgArray() {
        $result = $this->log->debug(array("test" => "test"));
        $this->assertEmpty($result['message']);
    }

    public function testMsgObjectWithoutToString() {
        $result = $this->log->debug((object) "test");
        $this->assertEmpty($result['message']);
    }

    public function testMsgObjectWithToString() {
        
    }

    public function testExceptionInContext() {
        $e = new Exception("42552b1f133f");
        $result = $this->log->debug('test', array('exception' => $e));
        $this->assertTrue(strpos($result['message'], "42552b1f133f") !== false);
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testInvalidLogLevel() {
        new log("invalid");
    }

    public function testInvalidLogMethod() {
        $result = $this->log->test("test");
        $this->assertFalse($result);
    }

}
