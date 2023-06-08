<?php

namespace Tests\AmazonPaymentServices\Model\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\InstallmentsPlansModel;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class InstallmentsPlansModelTest extends APSTestCase
{
    private ?InstallmentsPlansModel $installmentsPlans;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);

            $this->normalStatusParams = [
                'query_command'         => APSConstants::INSTALLMENTS_PLANS,
                'language'              => 'en',
            ];

            $this->normalPaymentParams['signature'] = (new APSSignature())->calculateSignature($this->normalStatusParams, true, $this->merchantConfig);

            $this->paymentCheckStatus = new InstallmentsPlansModel();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\InstallmentsPlansModel::getPaymentParameter
     *
     * @return void
     */
    public function testGetPaymentParameter(): void
    {
        try {
            $this->assertEquals(
                APSConstants::INSTALLMENTS_PLANS,
                $this->paymentCheckStatus->getPaymentParameter('query_command', $this->normalStatusParams, true)
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
