<?php

namespace Tests\AmazonPaymentServices\Model;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCheckStatusModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class PaymentDTOTest extends APSTestCase
{

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO::set
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO::getPaymentData
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO::setPaymentTypeAdapter
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO::getCurrencyDecimalPoints
     *
     * @return void
     */
    public function testAlmostAll(): void
    {
        try {
            $paymentDTO = new PaymentDTO($this->normalPaymentParams, new CCRedirectPurchase());

            $this->assertEquals(
                PaymentDTO::class,
                $paymentDTO->set('amount', 29.99)::class
            );

            $this->assertIsArray(
                $paymentDTO->getPaymentData()
            );

            $this->assertEquals(
                PaymentDTO::class,
                $paymentDTO->setPaymentTypeAdapter(new CCRedirectAuthorization())::class
            );

            $this->assertEquals(
                4,
                PaymentDTO::getCurrencyDecimalPoints('CLF')
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO::validate
     *
     * @return void
     */
    public function testValidate_noPaymentTypeAdapter(): void
    {
        try {
            $paymentDTO = new PaymentDTO($this->normalPaymentParams);
            $paymentDTO->validate();

            $this->fail('no exception thrown');
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PAYMENT_ADAPTER_MISSING,
                $e->getCode()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO::validate
     *
     * @return void
     */
    public function testValidate_missingRequiredParameter(): void
    {
        try {
            $paymentData = $this->normalPaymentParams;
            unset($paymentData['merchant_reference']);

            $paymentDTO = new PaymentDTO($paymentData, new PaymentCheckStatusModel());
            $paymentDTO->validate();

            $this->fail('no exception thrown');
        } catch (APSException $e) {
            $this->assertEquals(
                APSExceptionCodes::APS_PARAMETER_MISSING,
                $e->getCode(),
                $e->getMessage()
            );
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO::convertIntegerToAmount
     *
     * @return void
     */
    public function testConvertIntegerToAmount(): void
    {
        try {
            // with 4 decimal places
            $paymentData = $this->normalPaymentParams;
            $paymentData['currency'] = 'CLF';
            $paymentDTO = new PaymentDTO($paymentData);
            $this->assertEquals(
                $paymentDTO->getAmount(true),
                PaymentDTO::convertIntegerToAmount($paymentDTO->getAmount(false), $paymentData['currency'])
            );

            // with 0 decimal places
            $paymentData = $this->normalPaymentParams;
            $paymentData['currency'] = 'BIF';
            $paymentDTO = new PaymentDTO($paymentData);
            $this->assertEquals(
                $paymentDTO->getAmount(true),
                PaymentDTO::convertIntegerToAmount($paymentDTO->getAmount(false), $paymentData['currency'])
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
