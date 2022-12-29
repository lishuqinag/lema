<?php

namespace App\Service;

class AppLogger
{
    const TYPE_LOG4PHP = 'log4php';
    const TYPE_THINK_LOG = 'think-log';

    private $logger;

    public function __construct($type = self::TYPE_LOG4PHP)
    {
        if ($type == self::TYPE_LOG4PHP) {
            $this->logger = \Logger::getLogger("Log");
        } else if (self::TYPE_THINK_LOG) {
            $logger = \Logger::getLogger("think-log");
            $this->logger = new ThinkLogEXt($logger);
        }
    }

    public function info($message = '')
    {
        $this->logger->info($message);
    }

    public function debug($message = '')
    {
        $this->logger->debug($message);
    }

    public function error($message = '')
    {
        $this->logger->error($message);
    }
}

/**
 * 适配器模式 解决写入日志改为大写
 * @package App\Service
 */
class ThinkLogEXt {

    public $logger;

    public function __construct($logger)
    {
        $this->logger = $logger;
    }

    public function info($message = '')
    {
        $message = strtoupper($message);
        $this->logger->info($message);
    }

    public function debug($message = '')
    {
        $this->logger->debug($message);
    }

    public function error($message = '')
    {
        $this->logger->error($message);
    }

}
