<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Logger;

use Psr\Log\LoggerInterface;

class Logger
{
    private static ?LoggerInterface $logger = null;

    /**
     * Return the logged instance
     *
     * @return LoggerInterface
     */
    public static function getInstance(): LoggerInterface
    {
        if (null === self::$logger) {
            self::$logger = APSLogger::createLogger();
        }

        return self::$logger;
    }

    /**
     * Set a custom logger to be used via this SDK
     *
     * @param LoggerInterface $logger
     *
     * @return LoggerInterface
     */
    public static function setLogger(LoggerInterface $logger): LoggerInterface
    {
        self::$logger = $logger;

        return self::getInstance();
    }

    public static function resetLogger(): LoggerInterface
    {
        self::$logger = null;

        return self::getInstance();
    }
}