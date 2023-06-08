<?php

namespace Tests\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\PaymentTrustedModel;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\PaymentTrustedModel
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentTrustedModelTest extends APSTestCase
{
    /**
     * @var PaymentTrustedModel|null
     */
    private ?PaymentTrustedModel $paymentTrusted;

    public function setUp():void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->normalTrustedParams =[
                'command' => 'PURCHASE',
                'amount' => 1,
                'merchant_reference' => 'O-00001-857',
                'currency' => 'USD',
                'language' => 'en',
            ];
            $this->normalPaymentParams['signature'] = (new APSSignature())->calculateSignature($this->normalTrustedParams, true,$this->merchantConfig);

            $this->paymentTrusted = new PaymentTrustedModel();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\PaymentTrustedModel::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter(): void
    {
        try {
            $this->assertEquals(
                    APSConstants::PAYMENT_COMMAND_PURCHASE,
                $this->paymentTrusted->getPaymentParameter('command', null,true)
            );

            $cardSecurityCode = '123';
            $params['card_security_code'] = $cardSecurityCode;
            $this->assertEquals(
                    $cardSecurityCode,
                $this->paymentTrusted->getPaymentParameter('card_security_code', $cardSecurityCode, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }

    }
}
