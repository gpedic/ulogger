uLogger
=======

Simple logger for PHP without external dependencies.  
- Support for PSR-3 log levels (Psr\Log\LogLevel)
- Interface is similar to Psr\Log\LoggerInterface

Example
=======
```php
use uLogger\Logger as log;
use uLogger\Writer as writer;

//Example using FileWriterRotNum rotating log writer
//Limits: max size per log file 5MB, max file count 2
//Default: maxsize: 1MB, maxcount: 5
$limits = array('maxsize' => 5, 'maxcount' => 2);
$writer = new writer\FileWriterRotNum('log.txt', $limits);

//create logger with min log level ERROR
$logger = new log(log::ERROR, DateTime::ISO8601, new DateTimeZone('UTC'));

//attach writer to logger object
$logger->attachWriter($writer);

$logger->log(log:CRITICAL, "Can't recover from this.");
//[2014-10-30T14:20:28+0000][CRITICAL] Can't recover from this.

$logger->error("Something went wrong.");
//[2014-10-30T14:20:28+0000][ERROR] Something went wrong.

//basic PSR-3 interpolation support
$logger->error("I'm sorry, {user}. I'm afraid I can't do that.", array("user" => "Dave");
//[2014-10-30T14:20:28+0000][ERROR] I'm sorry, Dave. I'm afraid I can't do that.
```
