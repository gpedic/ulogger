<?php
use uLogger\uLogger as log;
include_once "src/uLogger.php";
include_once "src/writers/uLoggerFileWriter.php";

class uLoggerFileWriterTest extends PHPUnit_Framework_TestCase {

    protected $writer;
    protected $log;
    protected $limits = array('maxsize' => 1, 'maxcount'=> 5);
    protected $testfile;

    protected function setUp() {
        $this->testfile = __DIR__ . '/test.txt';
        $this->writer = new uLoggerFileWriter($this->testfile, $this->limits);
        $this->log = new log(log::ERROR);
        $this->log->attachWriter($this->writer);
        $this->clearAllLogFiles();
    }

    protected function clearAllLogFiles() {
        $logFiles = glob($this->testfile . '*');
        foreach ($logFiles as $filename) {
            unlink($filename);
        }
    }

    protected function stringWithLotsOfW ($bytesize) {
        return str_repeat('w', $bytesize);
    }

    protected function tearDown() {
        $this->clearAllLogFiles();
    }

    public function testNewLogFileIsCreated() {
        $this->log->error("Should create a log file.");
        $this->assertFileExists($this->testfile);
    }

    public function testNewFileCreatedAfterSizeLimitExceeded() {
        $this->log->error($this->stringWithLotsOfW(1024 * 1024));
        $this->log->error('Put it over the edge.');
        $this->assertFileExists($this->testfile);
        $this->assertFileExists($this->testfile . ".1");
        $logfiles = glob($this->testfile . '*');
        $this->assertEquals(count($logfiles), 2);
    }

    public function testNoAdditionalFilesCreatedAfterCountLimit() {
        $bigString = $this->stringWithLotsOfW(1024 * 1024);
        //create 5 log files
        for($i=0; $i<$this->limits['maxcount']; $i++) {
            $this->log->error($bigString);
        }
        //then write some additional stuff
        for($i=0; $i<100; $i++) {
            $this->log->error('Filecount shouldn\'t change.');
        }
        $logfiles = glob($this->testfile . '*');
        //filecount should still be maxcount
        $this->assertEquals(count($logfiles), 5);
    }

    public function testFileNumberingIsCorrect() {
        $bigString = $this->stringWithLotsOfW(1024 * 1024);
        for($i=0; $i<20; $i++) {
            $this->log->error($bigString);
            foreach(glob($this->testfile . '*') as $filename){
                
            }
        }
    }

    public function test() {
        
    }

}
