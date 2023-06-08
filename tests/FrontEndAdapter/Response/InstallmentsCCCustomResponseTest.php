<?php

namespace Tests\FrontEndAdapter\Response;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\InstallmentsCCCustomResponse;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCCustomPurchase
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\InstallmentsCCCustomResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class InstallmentsCCCustomResponseTest extends APSTestCase
{
    private InstallmentsCCCustomResponse $installmentsCCCustomResponse;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $this->installmentsCCCustomResponse = new InstallmentsCCCustomResponse($fakeResponseData);
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
    public function testProcess_3dSecureModal(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['3ds_url'] = 'http://test.com';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $this->installmentsCCCustomResponse = new InstallmentsCCCustomResponse($fakeResponseData);

            $this->assertTrue(true);
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
    public function testProcess_noSuccess(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '99999';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $this->installmentsCCCustomResponse = new InstallmentsCCCustomResponse($fakeResponseData);

            $this->assertNull($this->installmentsCCCustomResponse->process());
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
    public function testProcess_noAuthorization(): void
    {
        try {
            $fakeResponseData = $this->normalPaymentParams;
            $fakeResponseData['status'] = '02';
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($fakeResponseData, false, $this->merchantConfig);
            $this->installmentsCCCustomResponse = new InstallmentsCCCustomResponse($fakeResponseData);

            $this->assertNull($this->installmentsCCCustomResponse->process());
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

            $this->installmentsCCCustomResponse = new InstallmentsCCCustomResponse($fakeResponseData, $returnData);
            $fakeResponseData['signature'] = (new APSSignature())->calculateSignature($returnData, false, $this->merchantConfig);

            $mockCore = $this->createMock(APSCore::class);
            $mockCore
                ->method('callToAuthorizePayment')
                ->willReturn($returnData);

            $replaceApsConnector = function() use($mockCore) {
                $this->amazonPaymentServicesCore = $mockCore;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($this->installmentsCCCustomResponse, InstallmentsCCCustomResponse::class);
            $doReplaceApsConnector();

            $this->assertNull($this->installmentsCCCustomResponse->process());
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\InstallmentsCCCustomResponse::getSuccessStatusCodes
     *
     * @return void
     */
    public function testGetSuccessStatusCodes(): void
    {
        $statusCodes = $this->installmentsCCCustomResponse->getSuccessStatusCodes();

        $this->assertIsArray($statusCodes);
        $this->assertTrue(
            in_array('18', $statusCodes, true)
        );
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
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\InstallmentsCCCustomResponse::getPaymentOptions
     *
     * @return void
     */
    public function testGetPaymentOptions()
    {
        $paymentOptions = $this->installmentsCCCustomResponse->getPaymentOptions();

        $this->assertIsArray($paymentOptions);
        $this->assertEquals(
            APSConstants::PAYMENT_TYPE_INSTALMENTS,
            $paymentOptions['payment_type'] ?? ''
        );
        $this->assertEquals(
            APSConstants::INTEGRATION_TYPE_CUSTOM,
            $paymentOptions['integration_type'] ?? ''
        );
    }
}
