<?php declare(strict_types=1);


namespace AYashenkov\loggers;

use Psr\Log\LoggerInterface;

class FileLogger implements LoggerInterface
{
    /** @var mixed|string|null */
    private $dir;
    /** @var mixed|string */
    private $logFile;

    public function __construct($logDir = null)
    {
        if (!$logDir) {
            $logDir = $_ENV['LOG_DIR'] ? $_ENV['LOG_DIR'] : '';
        }
        $this->logFile = $_ENV['LOG_FILE'] ? $_ENV['LOG_FILE'] : 'noritsu_watch_errors.log';
        $this->dir = $logDir;
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function notice($message, array $context = array()): void
    {
        $this->logMessage('[NOTICE] '.$message);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function info($message, array $context = array()): void
    {
        $this->logMessage('[INFO] '.$message);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function debug($message, array $context = array()): void
    {
        $this->logMessage('[DEBUG] '.$message);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function emergency($message, array $context = array()): void
    {
        $this->logMessage('[EMERG] '.$message);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function critical($message, array $context = array()): void
    {
        $this->logMessage('[CRIT] '.$message);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function error($message, array $context = array()): void
    {
        $this->logMessage('[ERR] '.$message);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function warning($message, array $context = array()): void
    {
        $this->logMessage('[WARN] '.$message);
    }

    /**
     * @param string $message
     * @param array $context
     */
    public function alert($message, array $context = array()): void
    {
        $this->logMessage('[ALERT] '.$message);
    }

    /**
     * @param mixed $level
     * @param string $message
     * @param array $context
     */
    public function log($level, $message, array $context = array()): void
    {
        $this->logMessage($message);
    }

    /**
     * @param $message
     */
    private function logMessage($message): void
    {
        $time = date('Y-m-d H:i:s');
        file_put_contents($this->dir.$this->logFile, "[{$time}] {$message}\n", FILE_APPEND | LOCK_EX);
    }

}