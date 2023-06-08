<?php

namespace Tests\AmazonPaymentServices\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConnector;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\InstallmentsPlansModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCaptureModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCheckStatusModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentMotoModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentRecurringModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentRefundModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentVoidAuthorizationModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\PaymentTrustedModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use GuzzleHttp\Client;
use Tests\APSTestCase;
use Tests\TestGuzzleException;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCaptureModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConnector
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentVoidAuthorizationModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCheckStatusModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentRefundModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentRecurringModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\InstallmentsPlansModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentMotoModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\PaymentTrustedModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class APSCoreTest extends APSTestCase
{
    private ?APSCore $amazonPaymentServicesCore;

    private Client $client;

    public function setUp(): void
    {
        parent::setUp();

        APSMerchant::setMerchantParams($this->merchantConfig);
        $this->amazonPaymentServicesCore = new APSCore();
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::__construct
     *
     * @return void
     */
    public function testConstructor_noMerchantData(): void
    {
        try {
            APSMerchant::setMerchantParams([]);
            $this->amazonPaymentServicesCore = new APSCore();

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(APSExceptionCodes::MERCHANT_CONFIG_MISSING, $e->getCode());
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::calculateRequestSignature
     *
     * @return void
     */
    public function testCalculateRequestSignature(): void
    {
        $this->assertEquals(
            (new APSSignature())->calculateSignature($this->normalPaymentParams, true, $this->merchantConfig),
            $this->amazonPaymentServicesCore->calculateRequestSignature($this->normalPaymentParams)
        );
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::isResponseValid
     *
     * @return void
     */
    public function testIsResponseValid_noResponseSignature(): void
    {
        try {
            // build a fake response with NO response signature
            $responseData = $this->normalPaymentParams;

            $this->amazonPaymentServicesCore->isResponseValid($responseData);
        } catch (APSException $e) {
            $this->assertEquals(APSExceptionCodes::RESPONSE_NO_SIGNATURE, $e->getCode());
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::isResponseValid
     *
     * @return void
     */
    public function testIsResponseValid_badResponseSignature(): void
    {
        try {
            // build a fake response with a BAD response signature
            $responseData = $this->normalPaymentParams;
            $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, true, $this->merchantConfig);

            $this->assertEquals(
                APSValidator::isResponseValid($this->merchantConfig, $responseData),
                $this->amazonPaymentServicesCore->isResponseValid($responseData)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::isResponseValid
     *
     * @return void
     */
    public function testIsResponseValid_correctResponseSignature(): void
    {
        try {
            // build a fake response with the CORRECT response signature
            $responseData = $this->normalPaymentParams;
            $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);

            $this->assertEquals(
                APSValidator::isResponseValid($this->merchantConfig, $responseData),
                $this->amazonPaymentServicesCore->isResponseValid($responseData)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToAuthorizePayment
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToCapturePayment
     *
     * @return void
     */
    public function testCallToAuthorizePayment_noResponseSignature(): void
    {
        try {
            $this->buildCallToAuthorizePaymentCase([]);

            $paymentData = $this->normalPaymentParams;
            $paymentData['token_name'] = '432879fd7s8f79sd';
            $this->amazonPaymentServicesCore->callToAuthorizePayment(new PaymentDTO($paymentData, new CCStandardAuthorization()));

            $this->fail();
        } catch (APSException $e) {
            $this->assertContains(
                $e->getCode(),
                [
                    APSExceptionCodes::APS_PARAMETER_MISSING,
                    APSExceptionCodes::RESPONSE_NO_SIGNATURE
                ]
            );
        } catch (\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToAuthorizePayment
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToCapturePayment
     *
     * @return void
     */
    public function testCallToAuthorizePayment_badResponseSignature(): void
    {
        try {
            $this->buildCallToAuthorizePaymentCase([
                'signature' => 'bad',
            ]);

            $paymentData = $this->normalPaymentParams;
            $paymentData['token_name'] = '432879fd7s8f79sd';
            $paymentData['customer_ip'] = '127.0.0.1';

            $this->amazonPaymentServicesCore->callToAuthorizePayment(
                new PaymentDTO($paymentData, new CCStandardAuthorization())
            );

            $this->fail();
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_S2S_CALL_RESPONSE_SIGNATURE_FAILED,
                $e->getCode(),
                $e->getMessage(),
            );
        } catch (\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToAuthorizePayment
     *
     * @return void
     */
    public function testCallToAuthorizePayment_goodSignature(): void
    {
        try {
            $paymentTypeAdapter = new CCStandardAuthorization();

            $this->normalPaymentParams['token_name'] = 'test token name';
            $this->normalPaymentParams['customer_ip'] = $_SERVER['REMOTE_ADDR'] = '127.0.0.1';

	        $responseDTO = new PaymentDTO($this->normalPaymentParams, $paymentTypeAdapter);
	        $responseData = $responseDTO->getPaymentTypeAdapter()->generateParameters($responseDTO);

            $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);

	        $this->buildCallToAuthorizePaymentCase($responseData);

            $paymentData = $this->normalPaymentParams;
            $paymentData['token_name'] = '432879fd7s8f79sd';
            $paymentData['customer_ip'] = '127.0.0.1';

	        $actualResponseData = $this->amazonPaymentServicesCore->callToAuthorizePayment(new PaymentDTO($paymentData, $paymentTypeAdapter));

            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_AUTHORIZATION,
                $actualResponseData['command']
            );

            $this->assertEquals(
                $responseData,
                $actualResponseData
            );
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToAuthorizePayment
     *
     * @return void
     */
    public function testCallToCapturePayment_goodSignature(): void
    {
        try {
	        $paymentTypeAdapter = new PaymentCaptureModel();

	        $responseDTO = new PaymentDTO($this->normalPaymentParams, $paymentTypeAdapter);
	        $responseData = $responseDTO->getPaymentTypeAdapter()->generateParameters($responseDTO);

	        $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);

	        $this->buildCallToAuthorizePaymentCase($responseData);

            $paymentData = $this->normalPaymentParams;
            $paymentData['token_name'] = '432879fd7s8f79sd';
            $actualResponseData = $this->amazonPaymentServicesCore->callToCapturePayment(new PaymentDTO($paymentData, $paymentTypeAdapter));

            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_CAPTURE,
                $actualResponseData['command']
            );

            $this->assertEquals(
                $responseData,
                $actualResponseData
            );
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * Build the mock for authorize payment call
     *
     * @param array $returnData
     *
     * @return void
     *
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    private function buildCallToAuthorizePaymentCase(array $returnData): void
    {
        $mockConnector = $this->createMock(APSConnector::class);
        $mockConnector
            ->method('callToAps')
            ->willReturn($returnData);

        $replaceApsConnector = function() use($mockConnector) {
            $this->connector = $mockConnector;
        };
        $doReplaceApsConnector = $replaceApsConnector->bindTo($this->amazonPaymentServicesCore, APSCore::class);
        $doReplaceApsConnector();
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToRefundPayment
     *
     * @return void
     */
    public function testCallToRefundPayment_goodSignature(): void
    {
        try {
	        $paymentTypeAdapter = new PaymentRefundModel();

	        $responseDTO = new PaymentDTO($this->normalPaymentParams, $paymentTypeAdapter);
	        $responseData = $responseDTO->getPaymentTypeAdapter()->generateParameters($responseDTO);

	        $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);

            $this->buildCallToAuthorizePaymentCase($responseData);

            $paymentData = $this->normalPaymentParams;
            $paymentData['token_name'] = '432879fd7s8f79sd';
            $actualResponseData = $this->amazonPaymentServicesCore->callToRefundPayment(new PaymentDTO($paymentData, $paymentTypeAdapter));

            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_REFUND,
                $actualResponseData['command']
            );

            $this->assertEquals(
                $responseData,
                $actualResponseData
            );
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e){
            $this->fail($e->getMessage());
        }
    }

	/**
	 * @test
	 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToRecurringPayment
	 *
	 * @return void
	 */
	public function testCallToRecurringPayment_goodSignature(): void
	{
		try {
			$paymentTypeAdapter = new PaymentRecurringModel();

			$this->normalPaymentParams['token_name'] = '432879fd7s8f79sd';

			$responseDTO = new PaymentDTO($this->normalPaymentParams, $paymentTypeAdapter);
			$responseData = $responseDTO->getPaymentTypeAdapter()->generateParameters($responseDTO);

			$responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);

			$this->buildCallToAuthorizePaymentCase($responseData);

			$actualResponseData = $this->amazonPaymentServicesCore->callToRecurringPayment(new PaymentDTO($this->normalPaymentParams, $paymentTypeAdapter));

			$this->assertEquals(
				APSConstants::PAYMENT_COMMAND_PURCHASE,
				$actualResponseData['command']
			);

			$this->assertEquals(
				$responseData,
				$actualResponseData
			);
		} catch (APSException|\PHPUnit\Framework\MockObject\Exception $e){
			$this->fail($e->getMessage());
		}
	}

     /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToVoidPayment
     *
     * @return void
     */
    public function testCallToVoidPayment_goodSignature(): void
    {
        try {
	        $paymentTypeAdapter = new PaymentVoidAuthorizationModel();

	        $responseDTO = new PaymentDTO($this->normalPaymentParams, $paymentTypeAdapter);
	        $responseData = $responseDTO->getPaymentTypeAdapter()->generateParameters($responseDTO);

	        $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);

	        $this->buildCallToAuthorizePaymentCase($responseData);

            $paymentData = $this->normalPaymentParams;
            $paymentData['token_name'] = '432879fd7s8f79sd';
            $actualResponseData = $this->amazonPaymentServicesCore->callToVoidPayment(new PaymentDTO($paymentData, $paymentTypeAdapter));

            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_VOID,
                $actualResponseData['command']
            );

            $this->assertEquals(
                $responseData,
                $actualResponseData
            );
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToCheckPaymentStatus
     *
     * @return void
     */
    public function testCallToCheckPaymentStatus_goodSignature(): void
    {
        try {
	        $paymentTypeAdapter = new PaymentCheckStatusModel();

	        $responseDTO = new PaymentDTO($this->normalPaymentParams, $paymentTypeAdapter);
	        $responseData = $responseDTO->getPaymentTypeAdapter()->generateParameters($responseDTO);

	        $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);

	        $this->buildCallToAuthorizePaymentCase($responseData);

            $paymentData = $this->normalPaymentParams;
            $paymentData['token_name'] = '432879fd7s8f79sd';
            $actualResponseData = $this->amazonPaymentServicesCore->callToCheckPaymentStatus(new PaymentDTO($paymentData, $paymentTypeAdapter));

            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_CHECK_STATUS,
                $actualResponseData['query_command']
            );

            $this->assertEquals(
                $responseData,
                $actualResponseData
            );
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCallToMotoPayment_goodSignature(): void
    {
        try {
            $this->normalPaymentParams['token_name'] = '5540cb3b9e6a40e38227ab9141e7342a';
            $this->normalPaymentParams['customer_ip'] = '127.0.0.1';

            $paymentTypeAdapter = new PaymentMotoModel();
            $responseDTO = new PaymentDTO($this->normalPaymentParams, $paymentTypeAdapter);
            $responseData = $responseDTO->getPaymentTypeAdapter()->generateParameters($responseDTO);

            $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);
            $this->buildCallToAuthorizePaymentCase($responseData);

            $paymentData = $this->normalPaymentParams;

            $actualResponseData = $this->amazonPaymentServicesCore->callToMotoPayment(new PaymentDTO($paymentData, $paymentTypeAdapter));

            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_PURCHASE,
                $actualResponseData['command']
            );

            $this->assertEquals(
                $responseData,
                $actualResponseData
            );
        } catch (APSException|\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    public function testCallToTrustedPayment_goodSignature(): void
    {
        try {
            $this->normalPaymentParams['token_name'] = 'c5f29ce52ea74715b8612b3111e265cb';
            $this->normalPaymentParams['customer_ip'] = '127.0.0.1';
            $this->normalPaymentParams['eci'] = 'MOTO';

            $paymentTypeAdapter = new PaymentTrustedModel();
            $responseDTO = new PaymentDTO($this->normalPaymentParams, $paymentTypeAdapter);

            $responseData = $responseDTO->getPaymentTypeAdapter()->generateParameters($responseDTO);

            $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);
            $this->buildCallToAuthorizePaymentCase($responseData);

            $paymentData = $this->normalPaymentParams;
            $actualResponseData = $this->amazonPaymentServicesCore->callToTrustedPayment(new PaymentDTO($paymentData, $paymentTypeAdapter));

            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_PURCHASE,
                $actualResponseData['command']
            );

            $this->assertEquals(
                $responseData,
                $actualResponseData
            );
        } catch (APSException|\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::getInstallmentsPlans
     *
     * @return void
     */
    public function testGetInstallmentsPlans_goodSignature(): void
    {
        try {
	        $paymentTypeAdapter = new InstallmentsPlansModel();

	        $responseDTO = new PaymentDTO($this->normalPaymentParams, $paymentTypeAdapter);
	        $responseData = $responseDTO->getPaymentTypeAdapter()->generateParameters($responseDTO);

	        $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);

	        $this->buildCallToAuthorizePaymentCase($responseData);

            $paymentData = $this->normalPaymentParams;
            $actualResponseData = $this->amazonPaymentServicesCore->getInstallmentsPlans(new PaymentDTO($paymentData, $paymentTypeAdapter));

            $this->assertEquals(
                APSConstants::INSTALLMENTS_PLANS,
                $actualResponseData['query_command']
            );

            $this->assertEquals(
                $responseData,
                $actualResponseData
            );
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToAuthorizeApplePay
     *
     * @return void
     */
    public function testCallToAuthorizeApplePay(): void
    {
        try {
	        $paymentTypeAdapter = new ApplePayAuthorization();

            $applePayParams = array_merge($this->normalPaymentParams, [
                'digital_wallet'    => 'APPLE_PAY',
                'apple_data'        => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
                'apple_signature'   => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
                'apple_header'      => [
                    'apple_transactionId'      => '93eec76cbedaedca44648e3d5c314766906e4e78ce33cd3b8396f105a1c0daed',
                    'apple_ephemeralPublicKey' => 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEM9JqF04vDlGIHEzWsaDm4bGBlTJdCn3+DH8ptlAmOSwVddD7/FN93A2o+l7i2U6Lmjb8WhKJcz6ZB+2MabcF4g==',
                    'apple_publicKeyHash'      => 'bVTUiyTv0uCJgQz8SNYHBHOlHMD6sR1qDuCqTaETzkw=',
                ],
                'apple_paymentMethod'   => [
                    'apple_displayName'     => 'Visa 0492',
                    'apple_network'         => 'Visa',
                    'apple_type'            => 'debit',
                ],
                'customer_ip'           => '127.0.0.1',
            ]);

            $responseDTO = new PaymentDTO($applePayParams, $paymentTypeAdapter);
	        $responseData = $responseDTO->getPaymentTypeAdapter()->generateParameters($responseDTO);

            $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);


            $this->buildCallToAuthorizePaymentCase($responseData);

            $actualResponseData = $this->amazonPaymentServicesCore->callToAuthorizeApplePay(new PaymentDTO($applePayParams, $paymentTypeAdapter));

            $this->assertEquals(
                $responseData,
                $actualResponseData
            );
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToPurchaseApplePay
     *
     * @return void
     */
    public function testCallToPurchaseApplePay(): void
    {
        try {
	        $paymentTypeAdapter = new ApplePayPurchase();

            $applePayParams = array_merge($this->normalPaymentParams, [
                'digital_wallet'    => 'APPLE_PAY',
                'apple_data'        => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
                'apple_signature'   => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
                'apple_header'      => [
                    'apple_transactionId'      => '93eec76cbedaedca44648e3d5c314766906e4e78ce33cd3b8396f105a1c0daed',
                    'apple_ephemeralPublicKey' => 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEM9JqF04vDlGIHEzWsaDm4bGBlTJdCn3+DH8ptlAmOSwVddD7/FN93A2o+l7i2U6Lmjb8WhKJcz6ZB+2MabcF4g==',
                    'apple_publicKeyHash'      => 'bVTUiyTv0uCJgQz8SNYHBHOlHMD6sR1qDuCqTaETzkw=',
                ],
                'apple_paymentMethod'   => [
                    'apple_displayName'     => 'Visa 0492',
                    'apple_network'         => 'Visa',
                    'apple_type'            => 'debit',
                ],
                'customer_ip'           => '127.0.0.1',
            ]);

            $responseDTO = new PaymentDTO($applePayParams, $paymentTypeAdapter);
	        $responseData = $responseDTO->getPaymentTypeAdapter()->generateParameters($responseDTO);

            $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, false, $this->merchantConfig);


            $this->buildCallToAuthorizePaymentCase($responseData);

            $actualResponseData = $this->amazonPaymentServicesCore->callToPurchaseApplePay(new PaymentDTO($applePayParams, $paymentTypeAdapter));

            $this->assertEquals(
                $responseData,
                $actualResponseData
            );
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToPurchaseApplePay
     *
     * @return void
     */
    public function testCallToPurchaseApplePay_badResponseSignature(): void
    {
        try {
	        $paymentTypeAdapter = new ApplePayPurchase();

            $applePayParams = array_merge($this->normalPaymentParams, [
                'digital_wallet'    => 'APPLE_PAY',
                'apple_data'        => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
                'apple_signature'   => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
                'apple_header'      => [
                    'apple_transactionId'      => '93eec76cbedaedca44648e3d5c314766906e4e78ce33cd3b8396f105a1c0daed',
                    'apple_ephemeralPublicKey' => 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEM9JqF04vDlGIHEzWsaDm4bGBlTJdCn3+DH8ptlAmOSwVddD7/FN93A2o+l7i2U6Lmjb8WhKJcz6ZB+2MabcF4g==',
                    'apple_publicKeyHash'      => 'bVTUiyTv0uCJgQz8SNYHBHOlHMD6sR1qDuCqTaETzkw=',
                ],
                'apple_paymentMethod'   => [
                    'apple_displayName'     => 'Visa 0492',
                    'apple_network'         => 'Visa',
                    'apple_type'            => 'debit',
                ],
                'customer_ip'           => '127.0.0.1',
            ]);

            $responseDTO = new PaymentDTO($applePayParams, $paymentTypeAdapter);
	        $responseData = $responseDTO->getPaymentTypeAdapter()->generateParameters($responseDTO);

            $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, true, $this->merchantConfig);


            $this->buildCallToAuthorizePaymentCase($responseData);

            $this->amazonPaymentServicesCore->callToPurchaseApplePay(new PaymentDTO($applePayParams, $paymentTypeAdapter));
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_S2S_CALL_RESPONSE_SIGNATURE_FAILED,
                $e->getCode()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::callToPurchaseApplePay
     *
     * @return void
     */
    public function testCallToPurchaseApplePay_throwsGuzzleException(): void
    {
        try {
	        $paymentTypeAdapter = new ApplePayPurchase();

            $applePayParams = array_merge($this->normalPaymentParams, [
                'digital_wallet'    => 'APPLE_PAY',
                'apple_data'        => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
                'apple_signature'   => 'dsajdlsakjkldsjalkdsajkljdsakljdl',
                'apple_header'      => [
                    'apple_transactionId'      => '93eec76cbedaedca44648e3d5c314766906e4e78ce33cd3b8396f105a1c0daed',
                    'apple_ephemeralPublicKey' => 'MFkwEwYHKoZIzj0CAQYIKoZIzj0DAQcDQgAEM9JqF04vDlGIHEzWsaDm4bGBlTJdCn3+DH8ptlAmOSwVddD7/FN93A2o+l7i2U6Lmjb8WhKJcz6ZB+2MabcF4g==',
                    'apple_publicKeyHash'      => 'bVTUiyTv0uCJgQz8SNYHBHOlHMD6sR1qDuCqTaETzkw=',
                ],
                'apple_paymentMethod'   => [
                    'apple_displayName'     => 'Visa 0492',
                    'apple_network'         => 'Visa',
                    'apple_type'            => 'debit',
                ],
                'customer_ip'           => '127.0.0.1',
            ]);

            $responseDTO = new PaymentDTO($applePayParams, $paymentTypeAdapter);
	        $responseData = $responseDTO->getPaymentTypeAdapter()->generateParameters($responseDTO);

            $responseData['signature'] = (new APSSignature())->calculateSignature($responseData, true, $this->merchantConfig);


            $mockConnector = $this->createMock(APSConnector::class);
            $mockConnector
                ->method('callToAps')
                ->willThrowException(new TestGuzzleException());

            $replaceApsConnector = function() use($mockConnector) {
                $this->connector = $mockConnector;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($this->amazonPaymentServicesCore, APSCore::class);
            $doReplaceApsConnector();

            $this->amazonPaymentServicesCore->callToPurchaseApplePay(new PaymentDTO($applePayParams, $paymentTypeAdapter));
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_S2S_CALL_FAILED,
                $e->getCode()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore::getAppleCustomerSession
     *
     * @return void
     */
    public function testGetAppleCustomerSession(): void
    {
        try {
            $returnData = 'string';
            $mockConnector = $this->createMock(APSConnector::class);
            $mockConnector
                ->method('callToAps')
                ->willReturn($returnData);

            $replaceApsConnector = function() use($mockConnector) {
                $this->connector = $mockConnector;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($this->amazonPaymentServicesCore, APSCore::class);
            $doReplaceApsConnector();

            $appleUrl = 'https://pay.apple.com/';

            $this->assertEquals(
                $returnData,
                $this->amazonPaymentServicesCore->getAppleCustomerSession($appleUrl, $this->normalPaymentParams, [])
            );
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
