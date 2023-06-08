<?php

namespace Tests\FrontEndAdapter\Response;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomPurchase;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCCustomResponse;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCCustomResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\Secure3dsModal
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class CCCustomResponseTest extends APSTestCase
{
    private CCCustomResponse $creditCardCustomResponse;

    public function setUp(): void
    {
        parent::setUp();

        try {
            $this->merchantConfig['3ds_modal'] = true;
            APSMerchant::setMerchantParams($this->merchantConfig);

            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $this->creditCardCustomResponse = new CCCustomResponse($fakeResponseData);
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCCustomResponse::getSuccessStatusCodes
     *
     * @return void
     */
    public function testGetSuccessStatusCodes(): void
    {
        $statusCodes = $this->creditCardCustomResponse->getSuccessStatusCodes();

        $this->assertIsArray($statusCodes);
        $this->assertTrue(
            in_array('02', $statusCodes, true)
        );
        $this->assertTrue(
            in_array('14', $statusCodes, true)
        );
        $this->assertTrue(
            in_array('44', $statusCodes, true)
        );
        $this->assertTrue(
            in_array('20', $statusCodes, true)
        );
        $this->assertTrue(
            in_array('18', $statusCodes, true)
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCCustomResponse::getPaymentOptions
     *
     * @return void
     */
    public function testGetPaymentOptions(): void
    {
        $paymentOptions = $this->creditCardCustomResponse->getPaymentOptions();

        $this->assertIsArray($paymentOptions);
        $this->assertEquals(
            APSConstants::PAYMENT_TYPE_CREDIT_CARD,
            $paymentOptions['payment_type'] ?? ''
        );
        $this->assertEquals(
            APSConstants::INTEGRATION_TYPE_CUSTOM,
            $paymentOptions['integration_type'] ?? ''
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCCustomResponse::process
     *
     * @return void
     */
    public function testProcess_noSuccess(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '99999';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $this->creditCardCustomResponse = new CCCustomResponse($fakeResponseData);

            $this->assertNull($this->creditCardCustomResponse->process());
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCCustomResponse::process
     *
     * @return void
     */
    public function testProcess_noTokenization(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $this->creditCardCustomResponse = new CCCustomResponse($fakeResponseData);

            $this->assertNull($this->creditCardCustomResponse->process());
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCCustomResponse::process
     *
     * @return void
     */
    public function testProcess_3dSecureModal(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['3ds_url'] = 'http://test.com';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $this->creditCardCustomResponse = new CCCustomResponse($fakeResponseData);

            $this->assertIsString($this->creditCardCustomResponse->process());
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\InstallmentsCCCustomResponse::process
     *
     * @return void
     */
    public function testProcess_authorizationProcessAgain(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['token_name'] = 'fdhsjkfhdskjhfksdj';
            $fakeResponseData['merchant_reference'] = '000001';
            $fakeResponseData['customer_ip'] = '000001';
            $fakeResponseData['plan_code'] = '000001';
            $fakeResponseData['issuer_code'] = '000001';
            $fakeResponseData['service_command'] = APSConstants::PAYMENT_COMMAND_TOKENIZATION;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);

            $returnData = $this->normalPaymentParams;
            $returnData['status'] = '02';
            $returnData['merchant_reference'] = '000001';
            $returnData['customer_ip'] = '000001';
            $returnData['plan_code'] = '000001';
            $returnData['issuer_code'] = '000001';
            $fakeResponseData['command'] = APSConstants::PAYMENT_COMMAND_AUTHORIZATION;

            $this->creditCardCustomResponse = new CCCustomResponse($fakeResponseData, $returnData);
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($returnData, false, $this->merchantConfig);

            $mockCore = $this->createMock(APSCore::class);
            $mockCore
                ->method('callToAuthorizePayment')
                ->willReturn($returnData);

            $replaceApsConnector = function() use($mockCore) {
                $this->amazonPaymentServicesCore = $mockCore;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($this->creditCardCustomResponse, CCCustomResponse::class);
            $doReplaceApsConnector();

            $this->assertNull($this->creditCardCustomResponse->process());
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCCustomResponse::getDiscriminator
     *
     * @return void
     */
    public function testGetDiscriminator(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['3ds_url'] = 'http://test.com';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);

            $this->creditCardCustomResponse = new CCCustomResponse($fakeResponseData, [], true);
            $this->assertEquals(
                (new CCCustomPurchase())->getDiscriminator(),
                $this->creditCardCustomResponse->getDiscriminator()
            );

            $this->creditCardCustomResponse = new CCCustomResponse($fakeResponseData, [], false);
            $this->assertEquals(
                (new CCCustomAuthorization())->getDiscriminator(),
                $this->creditCardCustomResponse->getDiscriminator()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }

    }
}
