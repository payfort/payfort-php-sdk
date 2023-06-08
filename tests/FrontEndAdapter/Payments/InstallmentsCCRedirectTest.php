<?php

namespace Tests\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\InstallmentsCCRedirect;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCRedirectPurchase
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\InstallmentsCCRedirect
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class InstallmentsCCRedirectTest extends APSTestCase
{
    private InstallmentsCCRedirect $installmentsCCRedirect;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);
            $this->installmentsCCRedirect = new InstallmentsCCRedirect();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\InstallmentsCCRedirect::render
     *
     * @return void
     */
    public function testRender(): void
    {
        try {
            $this->installmentsCCRedirect->setPaymentData($this->normalPaymentParams);
            $this->installmentsCCRedirect->setCallbackUrl('https://test.com');

            $this->assertIsString($this->installmentsCCRedirect->render([]));
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
