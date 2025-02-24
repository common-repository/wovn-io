<?php
namespace Wovnio\Wovnwp\WpClasses;

/**
 * Logger implementation inspired by PSR3: https://www.php-fig.org/psr/psr-3/.
 * Unlike PSR3, the `log` function is private and there are no constants for
 * representing log levels.
 */
class Logger
{
    const PHP_SYSTEM_LOGGER = 0;
    const LOG_FILE = 3;

    private static $logger = null;

    private $prefix;
    private $quiet;
    private $destinationType;
    private $logFilePath;
    private $maxLogLineLength = 4096;
    private $uniqueId;

    public static function get()
    {
        if (self::$logger === null) {
            self::$logger = new Logger('UNKNOWN');
        }

        return self::$logger;
    }

    public static function set($logger)
    {
        self::$logger = $logger;
    }

    public function __construct($token, $quiet = true, $prefix = 'WOVN')
    {
        $this->uniqueId = $token . $this->getReasonableCurrentTime();
        date_default_timezone_set('UTC');
        $this->prefix = $prefix;
        $this->quiet = $quiet;
        $this->destinationType = self::PHP_SYSTEM_LOGGER;
    }

    public function setLogFilePath($logFilePath)
    {
        if (!empty($logFilePath)) {
            $this->logFilePath = $logFilePath;
            $this->destinationType = self::LOG_FILE;
        } else {
            $this->destinationType = self::PHP_SYSTEM_LOGGER;
        }
        $this->quiet = false;
    }

    public function setMaxLogLineLength($maxLength)
    {
        $this->maxLogLineLength = $maxLength;
    }

    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    public function getQuiet()
    {
        return $this->quiet;
    }

    public function setQuiet($quiet)
    {
        $this->quiet = $quiet;
    }

    public function emergency($message, $context = array())
    {
        $this->log('EMERGENCY', $message, $context);
    }

    public function alert($message, $context = array())
    {
        $this->log('ALERT', $message, $context);
    }

    public function critical($message, $context = array())
    {
        $this->log('CRITICAL', $message, $context);
    }

    public function error($message, $context = array())
    {
        $this->log('ERROR', $message, $context);
    }

    public function warning($message, $context = array())
    {
        $this->log('WARNING', $message, $context);
    }

    public function notice($message, $context = array())
    {
        $this->log('NOTICE', $message, $context);
    }

    public function info($message, $context = array())
    {
        $this->log('INFO', $message, $context);
    }

    public function debug($message, $context = array())
    {
        $this->log('DEBUG', $message, $context);
    }

    private function log($level, $message, $context)
    {
        if ($this->quiet) {
            return;
        }

        $date = date('Y-m-d H:i:s');
        $prefixString = "$this->prefix [$date][$level] ";

        $log_message = $this->truncateToLengthLimit($prefixString . $this->interpolate($message, $context));

        if ($this->destinationType == self::PHP_SYSTEM_LOGGER) {
            error_log($log_message);
        }

        if ($this->destinationType == self::LOG_FILE) {
            error_log($log_message . "\n", self::LOG_FILE, $this->logFilePath);
        }
    }

    private function interpolate($message, $context)
    {
        $replacements = array();

        foreach ($context as $key => $value) {
            if ($key === 'exception') {
                $replacements['{' . $key . '}'] = $value->getMessage();
            } elseif (!is_array($value) && (!is_object($value) || method_exists($value, '__toString'))) {
                $replacements['{' . $key . '}'] = $value;
            }
        }

        return strtr($message, $replacements);
    }

    private function truncateToLengthLimit($log)
    {
        if (strlen($log) < $this->maxLogLineLength) {
            return $log;
        }
        return substr($log, 0, $this->maxLogLineLength - 12) . '[TRUNCATED]';
    }

    private function getReasonableCurrentTime()
    {
        $currentTime = str_replace(' ', '', microtime());
        $currentTime = str_replace('0.', '', $currentTime);
        return $currentTime;
    }
}
