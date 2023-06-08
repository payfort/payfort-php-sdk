<?php

namespace Tests\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentApplePaySession;
use Tests\APSTestCase;
use Tests\APSTestLogging;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayHeaderOptions
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayInitialization
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentApplePaySession
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentApplePaySessionTest extends APSTestCase
{

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentApplePaySession::applePayValidateSession
     *
     * @return void
     */
    public function testApplePayValidateSession(): void
    {
        try {
            $this->merchantConfig['debug_mode'] = true;
            APSMerchant::setMerchantParams($this->merchantConfig);
            Logger::setLogger(new APSTestLogging());

            $appleUrl = 'https://pay.apple.com/';
            $paymentAppleSession = new PaymentApplePaySession();

            $returnData = 'string';

            $mockCore = $this->createMock(APSCore::class);
            $mockCore
                ->method('getAppleCustomerSession')
                ->willReturn($returnData);

            $replaceApsConnector = function() use($mockCore) {
                $this->amazonPaymentServicesCore = $mockCore;
            };
            $doReplaceApsConnector = $replaceApsConnector->bindTo($paymentAppleSession, PaymentApplePaySession::class);
            $doReplaceApsConnector();

            $this->assertIsString(
                $paymentAppleSession->applePayValidateSession($appleUrl)
            );
        } catch (APSException|\PHPUnit\Framework\MockObject\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
