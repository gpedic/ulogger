uLogger
=======

Simple logger for PHP without external dependencies.  


Example
=======
```php
use uLogger\Logger as log;
use uLogger\Writer as writer;

//FileWriterRotNum rotating log writer
$writer = new writer\FileWriterRotNum('log.txt');
$logger = new log(log::ERROR);
$logger->attachWriter($writer);

//then simply

$logger->log(log:CRITICAL, "Can't recover from this.");

//or

$logger->error("Something went wrong.");
```
