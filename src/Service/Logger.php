<?php
/**
 * Created by PhpStorm.
 * User: frank.hein
 * Date: 23.11.2018
 * Time: 12:11
 */

namespace Mxc\Shopware\Plugin\Service;


use Throwable;
use Traversable;
use Zend\Log\Logger as BaseLogger;

class Logger implements LoggerInterface
{
    /**
     * @var \Zend\Log\Logger $log
     */
    protected $log;

    public function __construct(BaseLogger $log) {
        $this->log = $log;
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function emerg($message, $extra = [])
    {
        $this->log->emerg($message, $extra);
        return $this;
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function alert($message, $extra = [])
    {
        $this->log->alert($message, $extra);
        return $this;
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function crit($message, $extra = [])
    {
        $this->log->crit($message, $extra);
        return $this;
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function err($message, $extra = [])
    {
        $this->log->err($message, $extra);
        return $this;
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function warn($message, $extra = [])
    {
        $this->log->warn($message, $extra);
        return $this;
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function notice($message, $extra = [])
    {
        $this->log->notice($message, $extra);
        return $this;
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function info($message, $extra = [])
    {
        $this->log->info($message, $extra);
        return $this;
    }

    /**
     * @param string $message
     * @param array|Traversable $extra
     * @return LoggerInterface
     */
    public function debug($message, $extra = [])
    {
        $this->log->debug($message, $extra);
        return $this;
    }

    public function enter() {
        return $this->logAction(true);
    }

    public function leave() {
        return $this->logAction(false);
    }

    public function except(Throwable $e, bool $logTrace = true, bool $rethrow = true) {
        $this->log->emerg(sprintf('%s: %s', get_class($e), $e->getMessage()));
        if ($logTrace) $this->log->emerg('Call stack: ' . PHP_EOL . $e->getTraceAsString());
        if ($rethrow)
            /** @noinspection PhpUnhandledExceptionInspection */
            throw($e);
        return $this;
    }

    protected function logAction(bool $start = true) {
        $marker = $start ? '>>> ' : '<<< ';
        $trace = debug_backtrace(DEBUG_BACKTRACE_PROVIDE_OBJECT, 3)[2];
        $this->log->debug(sprintf('%s %s#%s', $marker, $trace['class'], $trace['function']));
    }
}