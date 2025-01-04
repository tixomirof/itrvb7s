<?php

namespace ITRvB\Singletons;

use Monolog\Logger as MonologLogger;
use Psr\Log\LoggerInterface;
use Monolog\Handler\RotatingFileHandler;

class Logger
{
    protected static $logger = null;

    protected static function getLogger() : MonologLogger
    {
        if (self::$logger === null)
        {
            self::$logger = new MonologLogger('itrvb');
            $handler = new RotatingFileHandler('logs/debug.log');
            self::$logger->pushHandler($handler);
        }
        return self::$logger;
    }

    public static function info($msg)
    {
        self::getLogger()->info($msg);
    }

    public static function warning($msg)
    {
        self::getLogger()->warning($msg);
    }
}