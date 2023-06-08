<?php

namespace Tests\AmazonPaymentServices\Model;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCStandardPurchase
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 */
class APSResponseTest extends APSTestCase
{
    public function setUp(): void
    {
        parent::setUp();
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::isSuccess
     *
     * @return void
     */
    public function testIsSuccess_missingStatusParameter(): void
    {
        $amazonResponseModel = new APSResponse($this->normalPaymentParams, [], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertFalse(
            $amazonResponseModel->isSuccess()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::isSuccess
     *
     * @return void
     */
    public function testIsSuccess_wrongStatusParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['status'] = '20';
        $amazonResponseModel = new APSResponse($responseData, [], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertFalse(
            $amazonResponseModel->isSuccess()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::isSuccess
     *
     * @return void
     */
    public function testIsSuccess_wrongTypeStatusParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['status'] = '20';
        $amazonResponseModel = new APSResponse($responseData, [20], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertFalse(
            $amazonResponseModel->isSuccess()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::isSuccess
     *
     * @return void
     */
    public function testIsSuccess_correctTypeStatusParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['status'] = '20';
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertTrue(
            $amazonResponseModel->isSuccess()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::requires3dsValidation
     *
     * @return void
     */
    public function testRequires3dsValidation_parameterNotPresent(): void
    {
        $responseData = $this->normalPaymentParams;
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertFalse(
            $amazonResponseModel->requires3dsValidation()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::requires3dsValidation
     *
     * @return void
     */
    public function testRequires3dsValidation_parameterPresentButEmpty(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['3ds_url'] = '';
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertFalse(
            $amazonResponseModel->requires3dsValidation()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::requires3dsValidation
     *
     * @return void
     */
    public function testRequires3dsValidation_parameterPresent(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['3ds_url'] = 'https://3ds.validation';
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertTrue(
            $amazonResponseModel->requires3dsValidation()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::get3dsUrl
     *
     * @return void
     */
    public function testGet3dsUrl_noParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertNull(
            $amazonResponseModel->get3dsUrl()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::get3dsUrl
     *
     * @return void
     */
    public function testGet3dsUrl_emptyParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['3ds_url'] = '';
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertEmpty(
            $amazonResponseModel->get3dsUrl()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::get3dsUrl
     *
     * @return void
     */
    public function testGet3dsUrl_correctParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['3ds_url'] = 'https://3ds.validation';
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertEquals(
            'https://3ds.validation',
            $amazonResponseModel->get3dsUrl()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::isTokenization
     *
     * @return void
     */
    public function testIsTokenization_noParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertFalse(
            $amazonResponseModel->isTokenization()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::isTokenization
     *
     * @return void
     */
    public function testIsTokenization_emptyParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['service_command'] = '';
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertFalse(
            $amazonResponseModel->isTokenization()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::isTokenization
     *
     * @return void
     */
    public function testIsTokenization_correctParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['service_command'] = APSConstants::PAYMENT_COMMAND_TOKENIZATION;
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertTrue(
            $amazonResponseModel->isTokenization()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::getTokenName
     *
     * @return void
     */
    public function testGetTokenName_noParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertNull(
            $amazonResponseModel->getTokenName()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::getTokenName
     *
     * @return void
     */
    public function testGetTokenName_emptyParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['token_name'] = '';
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertEmpty(
            $amazonResponseModel->getTokenName()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::getTokenName
     *
     * @return void
     */
    public function testGetTokenName_correctParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['token_name'] = '1234567890';
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertEquals(
            '1234567890',
            $amazonResponseModel->getTokenName()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::getErrorMessage
     *
     * @return void
     */
    public function testGetErrorMessage_noParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertEquals(
            '-',
            $amazonResponseModel->getErrorMessage()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::getErrorMessage
     *
     * @return void
     */
    public function testGetErrorMessage_correctParameter(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['response_message'] = 'Success';
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertEquals(
            'Success',
            $amazonResponseModel->getErrorMessage()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::getResponseData
     *
     * @return void
     */
    public function testGetResponseData_unchanged(): void
    {
        $responseData = $this->normalPaymentParams;
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());

        $this->assertEquals(
            $responseData,
            $amazonResponseModel->getResponseData()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::getResponseData
     *
     * @return void
     */
    public function testGetResponseData_changed(): void
    {
        $responseData = $this->normalPaymentParams;
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());
        $responseData['command'] = APSConstants::PAYMENT_COMMAND_TOKENIZATION;

        $this->assertNotEquals(
            $responseData,
            $amazonResponseModel->getResponseData()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::setResponseMessage
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::getResponseMessage
     *
     * @return void
     */
    public function testGetSetResponseMessage(): void
    {
        $responseData = $this->normalPaymentParams;
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCRedirectAuthorization())->getDiscriminator());
        $amazonResponseModel->setResponseMessage('test');

        $this->assertEquals(
            'test',
            $amazonResponseModel->getResponseMessage()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::isStandardImplementation
     *
     * @return void
     */
    public function testIsStandardImplementation(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['status'] = '20';
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCStandardAuthorization())->getDiscriminator());

        $this->assertTrue(
            $amazonResponseModel->isStandardImplementation()
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse::getRedirectParams
     *
     * @return void
     */
    public function testGetRedirectParams(): void
    {
        $responseData = $this->normalPaymentParams;
        $responseData['status'] = '20';
        $amazonResponseModel = new APSResponse($responseData, ['20'], (new CCStandardAuthorization())->getDiscriminator());

        $this->assertStringContainsString(
            'status=20&',
            $amazonResponseModel->getRedirectParams()
        );
    }
}
