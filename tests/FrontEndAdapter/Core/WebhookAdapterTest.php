<?php

namespace Tests\FrontEndAdapter\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\WebhookAdapter;
use Psr\Log\LogLevel;
use Tests\APSTestCase;
use Tests\APSTestLogging;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class WebhookAdapterTest extends APSTestCase
{
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\WebhookAdapter::getWebhookData
     *
     * @return void
     */
    public function testGetWebhookData_noDataFromPhpInput(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $webhookParameters = WebhookAdapter::getWebhookData();

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::WEBHOOK_PARAMETERS_EMPTY,
                $e->getCode(),
                $e->getMessage()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\WebhookAdapter::getWebhookData
     *
     * @return void
     */
    public function testGetWebhookData_paramsStringLogging(): void
    {
        Logger::setLogger(new APSTestLogging());
        $this->merchantConfig['debug_mode'] = true;

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $webhookParameters = WebhookAdapter::getWebhookData('');

            $this->fail();
        } catch (APSException $e) {
            $this->assertTrue(
                Logger::getInstance()->isMessageStartInLogData('Webhook params string', LogLevel::DEBUG)
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\WebhookAdapter::getWebhookData
     *
     * @return void
     */
    public function testGetWebhookData_noPostData(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $webhookParameters = WebhookAdapter::getWebhookData('');

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::WEBHOOK_PARAMETERS_EMPTY,
                $e->getCode(),
                $e->getMessage()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\WebhookAdapter::getWebhookData
     *
     * @return void
     */
    public function testGetWebhookData_badPostData():void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $webhookParameters = WebhookAdapter::getWebhookData('{');

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::WEBHOOK_JSON_INVALID,
                $e->getCode(),
                $e->getMessage()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\WebhookAdapter::getWebhookData
     *
     * @return void
     */
    public function testGetWebhookData_paramsArrayLogging(): void
    {
        Logger::setLogger(new APSTestLogging());
        $this->merchantConfig['debug_mode'] = true;

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $webhookParameters = WebhookAdapter::getWebhookData(
                '{"data":"test","signature":"something"}'
            );

            $this->fail();
        } catch (APSException $e) {
            $this->assertTrue(
                Logger::getInstance()->isMessageStartInLogData('Webhook params: ', LogLevel::DEBUG)
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\WebhookAdapter::getWebhookData
     *
     * @return void
     */
    public function testGetWebhookData_badSignature(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $webhookParameters = WebhookAdapter::getWebhookData(
                '{"data":"test","signature":"something"}'
            );

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::WEBHOOK_SIGNATURE_INVALID,
                $e->getCode(),
                $e->getMessage()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\WebhookAdapter::getWebhookData
     *
     * @return void
     */
    public function testGetWebhookData_logValidResponse(): void
    {
        Logger::setLogger(new APSTestLogging());
        $this->merchantConfig['debug_mode'] = true;

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $webhookParameters = WebhookAdapter::getWebhookData(
                '{"data":"test","signature":"35af87c6def1a4ea40d485f680ab572cf867290b8e1850b3293a8323ecca7ec4"}'
            );

            $this->assertTrue(
                Logger::getInstance()->isMessageInLogData('Webhook signature is VALID!', LogLevel::INFO)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\WebhookAdapter::getWebhookData
     *
     * @return void
     */
    public function testGetWebhookData_validResponse(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $webhookParameters = WebhookAdapter::getWebhookData(
                '{"data":"test","signature":"35af87c6def1a4ea40d485f680ab572cf867290b8e1850b3293a8323ecca7ec4"}'
            );

            $this->assertEquals(
                APSResponse::class,
                $webhookParameters::class
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
