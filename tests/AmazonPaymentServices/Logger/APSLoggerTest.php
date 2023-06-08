<?php

namespace Tests\AmazonPaymentServices\Logger;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use Psr\Log\LogLevel;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class APSLoggerTest extends APSTestCase
{
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger::createLogger
     *
     * @return void
     */
    public function testCreateLogger_noMerchantParams(): void
    {
        try {
            APSLogger::createLogger();

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger::createLogger
     *
     * @return void
     */
    public function testCreateLogger_withMerchantParams(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);
            APSLogger::createLogger();

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger::log
     *
     * @return void
     */
    public function testLog_noMerchantParams(): void
    {
        try {
            APSLogger::createLogger()->log(LogLevel::INFO, 'test');

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger::log
     *
     * @return void
     */
    public function testLog_withMerchantParamsNoLogPath(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);
            APSLogger::createLogger()->log(LogLevel::INFO, 'test 123');

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger::log
     *
     * @return void
     */
    public function testLog_withMerchantParamsWithLogPath(): void
    {
        try {
            $this->merchantConfig['log_path'] = __DIR__ . '/tmp/aps.log';

            $this->assertFalse(
                file_exists($this->merchantConfig['log_path'])
            );

            APSMerchant::setMerchantParams($this->merchantConfig);
            APSLogger::createLogger()->log(LogLevel::INFO, 'test 123');

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}