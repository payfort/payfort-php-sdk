<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Logger;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use Psr\Log\LoggerInterface;
use Psr\Log\LoggerTrait;

class APSLogger implements LoggerInterface
{
    use LoggerTrait;

    private static ?string $logPath = null;

    private static ?string $identifier = null;

    private function __construct(?string $logPath)
    {
        self::$logPath = $logPath;
        self::$identifier = rand(10000, 99999);
    }

    /**
     * Get the logger instance
     *
     * @return static
     */
    public static function createLogger(): self
    {
        // check in global merchant configuration
        $merchantParams = APSMerchant::getMerchantParamsNoException();
        $logPath = $merchantParams['log_path'] ?? null;

        return new self($logPath);
    }

    /**
     * The log function
     * checks if logPath is defined, and appends log text to file
     *
     * @param mixed $level
     * @param \Stringable|string $message
     * @param array $context
     *
     * @return void
     */
    public function log(mixed $level, \Stringable|string $message, array $context = []): void
    {
        if (null === self::$logPath) {
            return;
        }

        file_put_contents(
            self::$logPath,
            date('Y-m-d H:i:s')
            . ' - (' . self::$identifier . ':' . strtoupper($level) . ') - '
            . $message
            . (!empty($context) ? PHP_EOL . json_encode($context) : '')
            . PHP_EOL,
            FILE_APPEND);
    }
}