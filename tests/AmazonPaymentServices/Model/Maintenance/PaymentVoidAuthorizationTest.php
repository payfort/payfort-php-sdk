<?php

namespace Tests\AmazonPaymentServices\Model\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentVoidAuthorizationModel;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentVoidAuthorizationTest extends APSTestCase
{
    private ?PaymentVoidAuthorizationModel $paymentVoidAuthorization;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->normalVoidParams = [
                'command'               => 'VOID_AUTHORIZATION',
                'amount'                => 1,
                'merchant_reference'    => 'O-00001-596',
                'currency'              => 'USD',
                'language'              => 'en',
            ];
            $this->normalPaymentParams['signature'] = (new APSSignature())->calculateSignature($this->normalVoidParams, true, $this->merchantConfig);

            // instantiate a child class
            $this->paymentVoidAuthorization = new PaymentVoidAuthorizationModel();
        } catch (APSException|\Exception $e) {
            $this->fail();
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentVoidAuthorizationModel::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter(): void
    {
        try {
            $this->assertEquals(
                APSConstants::PAYMENT_COMMAND_VOID,
                $this->paymentVoidAuthorization->getPaymentParameter('command', null, false)
            );
        } catch (APSException|\Exception $e) {
            $this->fail();
        }
    }
}
