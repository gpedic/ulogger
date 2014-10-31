<?php
namespace uLogger;
use uLogger\Writer\uLoggerWriter as Writer;

class Logger {

    private $writers = array();
    private $logLevel;
    private $timeFormat;

    /**
     * Severity levels defined in RFC 5424
     */
    const EMERGENCY = 0;
    const ALERT = 1;
    const CRITICAL = 2;
    const ERROR = 3;
    const WARNING = 4;
    const NOTICE = 5;
    const INFO = 6;
    const DEBUG = 7;

    /**
     * Map of log levels
     *
     * For getting the level name from the int value
     * @var array $levels
     */
    protected static $levels = array(
        0 => "EMERGENCY",
        1 => "ALERT",
        2 => "CRITICAL",
        3 => "ERROR",
        4 => "WARNING",
        5 => "NOTICE",
        6 => "INFO",
        7 => "DEBUG",
    );

    /**
     * Logger Constructor
     *
     * @param int $level
     * @param string $timeFormat
     * @throws \InvalidArgumentException
     */
    public function __construct($level = Logger::ERROR, $timeFormat = \DateTime::ISO8601) {
        if (!$this->setLogLevel($level)) {
            throw new \InvalidArgumentException("Invalid log level.");
        }
        if (!is_string($timeFormat)) {
            throw new \InvalidArgumentException("Invalid time format.");
        }
        if (!empty($timeFormat)) {
            $this->timeFormat = $timeFormat;
        }
    }

    /**
     * Attach a writer instance
     *
     * @param Writer $writer uLogger Writer instance
     */
    public function attachWriter(Writer $writer) {
        if (isset($writer)) {
            $this->writers[] = $writer;
        }
    }

    /**
     * Send new log entry to all writers
     * @param array $record
     */
    protected function notifyWriters(array $record) {
        foreach ($this->writers as $writer) {
            $writer->write($record);
        }
    }

    /**
     * Enable calling by log level method name
     *
     * @param string $name Log level name
     * @param array $args
     */
    public function __call($name, $args) {
        $level = static::resolveLogLevel($name);
        if (static::logLevelValid($level) && isset($args[0])) {
            $context = isset($args[1]) && is_array($args[1]) ? $args[1] : null;
            return $this->log($level, $args[0], $context);
        }
        return false;
    }

    /**
     * Log message
     *
     * @param int $level Integer value of log level
     * @param string|array $messages A string or array of strings
     *
     */
    public function log($level, $message, array $context = null) {
        $level = static::resolveLogLevel($level);
        if (!static::logLevelValid($level) || !$this->shouldBeLogged($level)) {
            return false;
        }

        $timestamp = static::getTimestamp($this->timeFormat);
        $msg = static::processLogMsg($message, $context);
        $record = array(
            'message' => $msg,
            'context' => $context,
            'level' => $level,
            'level_name' => static::$levels[$level],
            'datetime' => $timestamp,
            'extra' => array()
        );
        $this->notifyWriters($record);

        return $record;
    }

    /**
     * Process messages for logging
     *
     * @param string|array $messages
     * @return string
     */
    protected static function processLogMsg($message, array $context = null) {
		if (is_array($message)) {
			$message = var_export($message, true);
		}
        else if (!is_string($message) && static::canBeCastToString($message)) {
            $message = (string) $message;
        }
		elseif (is_array($context) && is_string($message)) {
            $message = static::interpolate($message, $context);
        }
        if (isset($context) && isset($context["exception"])
                && $context["exception"] instanceof \Exception) {
            $message .= PHP_EOL . ((string) $context["exception"]);
        }
        return $message;
    }

    /**
     * Set log level
     *
     * @param int $level
     * @return boolean
     */
    public function setLogLevel($level) {
        $intLevel = static::resolveLogLevel($level);
        if (static::logLevelValid($intLevel)) {
            $this->logLevel = $intLevel;
            return true;
        }
        return false;
    }

    protected function contextContainsException () {

    }

    /**
     * Get log level int value
     *
     * Resolve string level values to integers
     *
     * @param int|string $level
     * @return boolean
     */
    protected static function resolveLogLevel($level) {
        if (is_int($level)) {
            return $level;
        }
        else if (is_string($level)) {
            $lvlStrUpper = strtoupper($level);
            if (defined("self::$lvlStrUpper")) {
                return constant("self::$lvlStrUpper");
            }
        }
        return false;
    }

    /**
     * Check if message should be logged
     *
     * @param int $msgLevel
     * @param int $logLevel
     * @return boolean
     */
    protected function shouldBeLogged($msgLevel) {
        return $msgLevel <= $this->logLevel ? true : false;
    }

    /**
     * Check log level exists
     *
     * @param int $level
     * @return boolean
     */
    protected static function logLevelValid($level) {
        if (is_int($level) && isset(static::$levels[$level])) {
            return true;
        }
        return false;
    }

    /**
     * Get timestamp with microsecond precision
     *
     * @param string $timeFormat The time format to return
     * @return string
     */
    protected static function getTimestamp($timeFormat) {
        $timestamp = microtime(true);
        $micro = sprintf("%06d", ($timestamp - floor($timestamp)) * 1000000);
        $date = new \DateTime(date("Y-m-d H:i:s." . $micro, $timestamp));

        return $date->format($timeFormat);
    }

    /**
     *
     * @param type $message
     * @param array $context
     * @return type
     */
    protected static function interpolate($message, array $context) {
        if (strpos($message, '{') === false) {
            return $message;
        }
        $replace = array();
        foreach ($context as $key => $val) {
            if (static::canBeCastToString($val)) {
                $replace["{" . $key . "}"] = (string) $val;
            }
        }
        return strtr($message, $replace);
    }

    /**
     * Check if value can be cast to string
     *
     * @param any $value
     * @return boolean
     */
    protected static function canBeCastToString($value) {
        if (is_object($value) && method_exists($value, "__toString")) {
            return true;
        }
        return is_scalar($value) || is_null($value);
    }

}
