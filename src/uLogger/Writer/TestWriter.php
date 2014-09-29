<?php
namespace uLogger\Writer;
require_once "WriterInterface.php";

class TestWriter implements uLoggerWriter {
    public function write (array $record) {
        return $record;
    }
}
