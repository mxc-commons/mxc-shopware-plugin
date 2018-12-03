<?php

namespace Mxc\Shopware\Plugin;

use Mxc\Shopware\Plugin\Service\LoggerInterface;
use Zend\Config\Config;

abstract class ActionListener {

    /**
     * @var Config $config
     */
    protected $config;
    /**
     * @var LoggerInterface $log
     */
    protected $log;

    public function __construct(Config $config, LoggerInterface $log) {
        $this->config = $config;
        $this->log = $log;
    }

    protected function getOptions() {
        $function = debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS, 2)[1]['function'];
        /** @noinspection PhpUndefinedFieldInspection */
        $options = $this->config->options->$function ?? new Config([]);;
        $this->log->info('getOptions: function -> ' . $function . '$options -> ' . var_export($options->toArray(), true));
        /** @noinspection PhpUndefinedFieldInspection */
        $general = $this->config->general ?? new Config([]);
        $options->merge($general);
        return $options;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function set(array $members = []) {
        foreach ($members as $name => $value) {
            if (property_exists($this, $name)) {
                $this->$name = $value;
            }
        }
    }
}