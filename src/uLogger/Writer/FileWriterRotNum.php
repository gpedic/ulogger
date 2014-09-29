<?php
namespace uLogger\Writer;
require_once "WriterInterface.php";

class FileWriterRotNum implements uLoggerWriter {

    protected $filename;
    protected $filepath;
    protected $path;
    private $mode;
    protected static $logfile = null;
    protected $limits = array(
        "maxsize" => 1,
        "maxcount" => 5
    );

    public function __construct($filepath, array $limits = array(), $append = true) {
        $this->filename = basename($filepath);
        $this->filepath = $filepath;
        $this->path = dirname($filepath);
        $this->mode = $append ? 'a' : 'w';
        $this->setupLimits($limits);
        static::$logfile = fopen($this->filepath, $this->mode);
    }

    private function setupLimits($limits) {
        foreach ($limits as $name => $val) {
            if (isset($name, $this->limits) && is_int($val)) {
                $this->limits[$name] = $val;
            }
        }
        $this->limits["maxsize"] *= 1048576;
    }

    /**
     * Write log message to file
     * 
     * @param array $record
     */
    public function write(array $record) {
        clearstatcache();
        if (file_exists($this->filepath) && $this->logSizeOutsideLimit()) {
            if(is_resource(static::$logfile)) {
                fclose(static::$logfile);
            }
            $this->rotateLogFiles();
            static::$logfile = fopen($this->filepath, $this->mode);
        }
        $outputMsg = "[{$record['datetime']}][{$record['level_name']}] {$record['message']}" . PHP_EOL;
        fwrite(static::$logfile, $outputMsg);
        return $record;
    }

    /**
     * Check if log file is bigger than the limit
     * 
     * @return boolean
     */
    private function logSizeOutsideLimit() {
        if ($this->limits["maxsize"] > 0) {
            return filesize($this->filepath) >= $this->limits["maxsize"];
        }
        return false;
    }

    private function rotateLogFiles() {
        $logFileList = $this->getLogFileList();

        //one-up or delete the log files
        $logFileCount = count($logFileList);
        for ($idx = $logFileCount; $idx > 0; $idx--) {
            if ($this->limits["maxcount"] - $idx < 1) {
                unlink($logFileList[$idx - 1]);
            } else {
                rename($logFileList[$idx - 1], $this->filepath . ".$idx");
            }
        }
    }

    private function getLogFileList() {
        $logFileList = array();

        $it = new \FilesystemIterator($this->path);
        foreach ($it as $fileinfo) {
            if (strpos($fileinfo->getFilename(), $this->filename) === 0) {
                $logNum = intval($fileinfo->getExtension());
                $logFileList[$logNum] = $fileinfo->getPathname();
            }
        }

        return $logFileList;
    }

    public function __destruct() {
        if (is_resource(static::$logfile)) {
            fclose(static::$logfile);
        }
    }
}
