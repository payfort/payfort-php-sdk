<?php

namespace Tests\AmazonPaymentServices\Logger;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 */
class LoggerTest extends APSTestCase
{
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger::getInstance
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger::resetLogger
     *
     * @return void
     */
    public function testGetInstance_createLogger(): void
    {
        try {
            Logger::resetLogger()->log(LogLevel::INFO, 'test');

            // it worked, didn't give any error
            $this->assertTrue(true);
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger::setLogger
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger::getInstance
     *
     * @return void
     */
    public function testGetInstance_setLogger(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $logText = 'PHP SDK';
            $mockLogger = $this->createMock(LoggerInterface::class);
            Logger::setLogger($mockLogger);

            $this->assertTrue(
                str_contains(Logger::getInstance()::class, 'LoggerInterface')
            );

            Logger::getInstance()->log(LogLevel::INFO, 'test');

            // it worked, didn't give any error
            $this->assertTrue(true);
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

}
