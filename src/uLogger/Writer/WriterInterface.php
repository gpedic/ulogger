<?php
namespace uLogger\Writer;
interface uLoggerWriter {
    public function write(array $record);
}
