<?php

namespace Tests\FrontEndAdapter\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCStandardResponse;
use Psr\Log\LogLevel;
use Tests\APSTestCase;
use Tests\APSTestLogging;

/**
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCStandardResponse
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class ResponseAdapterTest extends APSTestCase
{
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter::__construct
     *
     * @return void
     */
    public function testConstruct(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);


            $responseAdapter = new CCRedirectResponse($fakeResponseData, $this->normalPaymentParams);

            $this->assertTrue(true);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter::handleAuthorization
     *
     * @return void
     */
    public function testHandleAuthorization_noAuhtorizationData(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['token_name'] = 'fake_token';
            $fakeResponseData['service_command'] = APSConstants::PAYMENT_COMMAND_TOKENIZATION;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $responseAdapter = new CCStandardResponse($fakeResponseData, null, false);
            $responseAdapter->process();

            $this->fail('no exception thrown');
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::PAYMENT_DATA_CONFIG_MISSING,
                $e->getCode()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter::handleAuthorization
     *
     * @return void
     */
    public function testHandleAuthorization_noTokenName(): void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['service_command'] = APSConstants::PAYMENT_COMMAND_TOKENIZATION;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);

            $fakePaymentData = [];
            $responseAdapter = new CCStandardResponse($fakeResponseData, $fakePaymentData, false);
            $responseAdapter->process();

            $this->fail('no exception thrown');
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_TOKEN_NAME_MISSING,
                $e->getCode()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter::handleSecure3ds
     *
     * @return void
     */
    public function testHandleSecure3ds_3dsUrlHeaderRedirect(): void
    {
        try {
            $this->merchantConfig['3ds_modal'] = false;
            $this->merchantConfig['debug_mode'] = true;
            APSMerchant::setMerchantParams($this->merchantConfig);
            Logger::setLogger(new APSTestLogging());

            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['3ds_url'] = 'https://hello.com';
            $fakeResponseData['command'] = APSConstants::PAYMENT_COMMAND_AUTHORIZATION;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);

            $fakePaymentData = [];
            $responseAdapter = new CCStandardResponse($fakeResponseData, $fakePaymentData, false);
            $responseAdapter->process();

            $this->assertTrue(
                Logger::getInstance()->isMessageStartInLogData('3ds Validation: URL', LogLevel::DEBUG)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
