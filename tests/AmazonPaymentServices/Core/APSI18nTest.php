<?php

namespace Tests\AmazonPaymentServices\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use PHPUnit\Framework\TestCase;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 */
class APSI18nTest extends APSTestCase
{
    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n::getText
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n::populateTextsArray
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n::loadTexts
     *
     * @return void
     */
    public function testGetText_noMerchantSet(): void
    {
        try {
            $this->assertIsString(
                APSI18n::getText('aps_s2s_call_failed')
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n::getText
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n::populateTextsArray
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n::loadTexts
     *
     * @return void
     */
    public function testGetText_noLocaleSet(): void
    {
        try {
            $merchantConfig = $this->merchantConfig;
            unset($merchantConfig['locale']);
            APSMerchant::setMerchantParams($merchantConfig);

            $this->assertIsString(
                APSI18n::getText('aps_s2s_call_failed')
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n::getText
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n::populateTextsArray
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n::loadTexts
     *
     * @return void
     */
    public function testGetText_otherLocaleSet(): void
    {
        try {
            $merchantConfig = $this->merchantConfig;
            $merchantConfig['locale'] = 'tr';
            APSMerchant::setMerchantParams($merchantConfig);

            $this->assertIsString(
                APSI18n::getText('aps_s2s_call_failed')
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n::getText
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n::populateTextsArray
     * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n::loadTexts
     *
     * @return void
     */
    public function testGetText_withParams(): void
    {
        try {
            $merchantConfig = $this->merchantConfig;
            $merchantConfig['locale'] = 'ar';
            APSMerchant::setMerchantParams($merchantConfig);

            $this->assertIsString(
                APSI18n::getText('aps_s2s_call_failed', [
                    'dummy_parameter'   => 'dummy_value',
                ])
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
