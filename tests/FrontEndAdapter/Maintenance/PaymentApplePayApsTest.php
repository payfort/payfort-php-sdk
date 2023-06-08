<?php

namespace Tests\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentApplePayAps;
use Psr\Log\LogLevel;
use Tests\APSTestCase;
use Tests\APSTestLogging;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentApplePayAps
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConnector
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentApplePayApsTest extends APSTestCase
{
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentApplePayAps::applePayAuthorization
     *
     * @return void
     */
    public function testApplePayAuthorization_noMerchantData():void
    {
        try {
            $data = (new PaymentApplePayAps())->applePayAuthorization($this->normalPaymentParams);

            $this->fail();
        } catch (APSException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentApplePayAps::applePayAuthorization
     *
     * @return void
     */
    public function testApplePayAuthorization_withMerchantData():void
    {
        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

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

            $_POST['data'] = [];
            $_POST['data']['paymentData'] = [];
            $_POST['data']['paymentData']['data'] = $applePayParams['apple_data'];
            $_POST['data']['paymentData']['signature'] = $applePayParams['apple_signature'];

            $_POST['data']['paymentData']['header'] = [];
            $_POST['data']['paymentData']['header']['transactionId'] = $applePayParams['apple_header']['apple_transactionId'];
            $_POST['data']['paymentData']['header']['publicKeyHash'] = $applePayParams['apple_header']['apple_publicKeyHash'];
            $_POST['data']['paymentData']['header']['ephemeralPublicKey'] = $applePayParams['apple_header']['apple_ephemeralPublicKey'];

            $_POST['data']['paymentMethod'] = [];
            $_POST['data']['paymentMethod']['displayName'] = $applePayParams['apple_paymentMethod']['apple_displayName'];
            $_POST['data']['paymentMethod']['network'] = $applePayParams['apple_paymentMethod']['apple_network'];
            $_POST['data']['paymentMethod']['type'] = $applePayParams['apple_paymentMethod']['apple_type'];

            $paymentApplePayAps = new PaymentApplePayAps();

            $returnData = [
                'test1' => 'test2',
            ];

            $mockCore = $this->createMock(APSCore::class);
            $mockCore
                ->method('callToAuthorizeApplePay')
                ->willReturn($returnData);

            $replaceApsConnector = function() use($mockCore) {
                $this->amazonPaymentServicesCore = $mockCore;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($paymentApplePayAps, PaymentApplePayAps::class);
            $doReplaceApsConnector();


            $this->assertEquals(
                $returnData,
                $paymentApplePayAps->applePayAuthorization($applePayParams)
            );
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentApplePayAps::applePayPurchase
     *
     * @return void
     */
    public function testApplePayPurchase_noMerchantData(): void
    {
        try {
            $data = (new PaymentApplePayAps())->applePayPurchase($this->normalPaymentParams);

            $this->fail();
        } catch (APSException $e) {
            $this->assertTrue(true, $e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentApplePayAps::applePayPurchase
     *
     * @return void
     */
    public function testApplePayPurchase_noAppleData(): void
    {
        try {
            $this->merchantConfig['debug_mode'] = true;
            APSMerchant::setMerchantParams($this->merchantConfig);
            Logger::setLogger(new APSTestLogging());

            $_POST = null;
            $data = (new PaymentApplePayAps())->applePayPurchase($this->normalPaymentParams);

            $this->fail();
        } catch (APSException $e) {
            $this->assertTrue(true, $e->getMessage());

            $this->assertTrue(
                Logger::getInstance()->isMessageStartInLogData('Apple Pay: Safari data received for APS call', LogLevel::DEBUG)
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentApplePayAps::applePayPurchase
     *
     * @return void
     */
    public function testApplePayPurchase_withMerchantData():void
    {
        try {
            $this->merchantConfig['debug_mode'] = true;
            APSMerchant::setMerchantParams($this->merchantConfig);
            Logger::setLogger(new APSTestLogging());

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

            $_POST['data'] = [];
            $_POST['data']['paymentData'] = [];
            $_POST['data']['paymentData']['data'] = $applePayParams['apple_data'];
            $_POST['data']['paymentData']['signature'] = $applePayParams['apple_signature'];

            $_POST['data']['paymentData']['header'] = [];
            $_POST['data']['paymentData']['header']['transactionId'] = $applePayParams['apple_header']['apple_transactionId'];
            $_POST['data']['paymentData']['header']['publicKeyHash'] = $applePayParams['apple_header']['apple_publicKeyHash'];
            $_POST['data']['paymentData']['header']['ephemeralPublicKey'] = $applePayParams['apple_header']['apple_ephemeralPublicKey'];

            $_POST['data']['paymentMethod'] = [];
            $_POST['data']['paymentMethod']['displayName'] = $applePayParams['apple_paymentMethod']['apple_displayName'];
            $_POST['data']['paymentMethod']['network'] = $applePayParams['apple_paymentMethod']['apple_network'];
            $_POST['data']['paymentMethod']['type'] = $applePayParams['apple_paymentMethod']['apple_type'];

            $paymentApplePayAps = new PaymentApplePayAps();

            $returnData = [
                'test1' => 'test2',
            ];

            $mockCore = $this->createMock(APSCore::class);
            $mockCore
                ->method('callToPurchaseApplePay')
                ->willReturn($returnData);

            $replaceApsConnector = function() use($mockCore) {
                $this->amazonPaymentServicesCore = $mockCore;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($paymentApplePayAps, PaymentApplePayAps::class);
            $doReplaceApsConnector();

            $this->assertEquals(
                $returnData,
                $paymentApplePayAps->applePayPurchase($applePayParams)
            );

            $this->assertTrue(
                Logger::getInstance()->isMessageStartInLogData('Apple Pay: Payment data after input transformation', LogLevel::DEBUG)
            );

        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
