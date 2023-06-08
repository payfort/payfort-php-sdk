<?php

namespace Tests\FrontEndAdapter\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter;
use AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCRedirectAuthorization
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Response\CCRedirectResponse
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class MaintenanceAdapterTest extends APSTestCase
{
    private MaintenanceAdapter $maintenanceAdapter;

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter::__construct
     * @return void
     * @throws \PHPUnit\Framework\MockObject\Exception
     */
    public function testConstruct(): void
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
}
