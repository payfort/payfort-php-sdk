<?php

namespace Tests\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePaySession;
use Tests\APSTestCase;
/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePaySession
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class ApplePaySessionTest extends APSTestCase
{
    private ?ApplePaySession $applePaySession;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            // instantiate a child class
            $this->applePaySession = new ApplePaySession();
        } catch (APSException|\Exception $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter_command(): void
    {
        try {
            $this->assertEquals(
                null,
                $this->applePaySession->getPaymentParameter('command', null, false)
            );
        } catch (APSException|\Exception $e) {
            $this->fail($e->getMessage());
        }
    }
}
