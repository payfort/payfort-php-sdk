<?php

namespace Tests\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayHeaderOptions;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayHeaderOptions
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class ApplePayHeaderOptionsTest extends APSTestCase
{
    private ApplePayHeaderOptions $applePayHeaderOptions;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->applePayHeaderOptions = new ApplePayHeaderOptions();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayHeaderOptions::__construct
     *
     * @return void
     */
    public function test__construct(): void
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
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayHeaderOptions::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_defaultReturn(): void
    {
        $this->assertTrue(
            in_array(
                'user_Agent',
                $this->applePayHeaderOptions->getRequiredParameters(),
                true
            )
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayHeaderOptions::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_parameters(): void
    {
        try {
            $this->assertIsString(
                $this->applePayHeaderOptions->getPaymentParameter('user_Agent', null, true)
            );

            $this->assertIsArray(
                $this->applePayHeaderOptions->getPaymentParameter('headers', null, true),
            );

            $this->assertIsNumeric(
                $this->applePayHeaderOptions->getPaymentParameter('timeout', null, true)
            );

            $this->assertIsNumeric(
                $this->applePayHeaderOptions->getPaymentParameter('redirection', null, true)
            );

            $this->assertIsBool(
                $this->applePayHeaderOptions->getPaymentParameter('sslverify', null, true)
            );

            $this->assertIsBool(
                $this->applePayHeaderOptions->getPaymentParameter('blocking', null, true)
            );

            $this->assertIsString(
                $this->applePayHeaderOptions->getPaymentParameter('ssl_cert', null, true)
            );

            $this->assertIsString(
                $this->applePayHeaderOptions->getPaymentParameter('sslcertificates', null, true)
            );

            $this->assertIsString(
                $this->applePayHeaderOptions->getPaymentParameter('httpversion', null, true)
            );

            $this->assertIsString(
                $this->applePayHeaderOptions->getPaymentParameter('data_format', null, true)
            );

            $this->assertIsArray(
                $this->applePayHeaderOptions->getPaymentParameter('ssl_key', null, true)
            );

            $this->assertIsArray(
                $this->applePayHeaderOptions->getPaymentParameter('curl', null, true)
            );

            $this->assertNull(
                $this->applePayHeaderOptions->getPaymentParameter('other_parameter', null, false)
            );

            $test = 'string';
            $this->assertEquals(
                $test,
                $this->applePayHeaderOptions->getPaymentParameter('other_parameter', $test, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }


    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayHeaderOptions::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_throwsException(): void
    {
        try {
            $this->applePayHeaderOptions->getPaymentParameter('other_parameter', null, true);

            $this->fail('no exception thrown');
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PARAMETER_MISSING,
                $e->getCode()
            );
        }
    }

}
