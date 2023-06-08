<?php

namespace Tests\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\InstallmentsCCCustom;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomTokenization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardTokenization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCCustomPurchase
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCStandard
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\InstallmentsCCCustom
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class InstallmentsCCCustomTest extends CCStandardTest
{
    private InstallmentsCCCustom $installmentsCCCustom;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);
            $this->installmentsCCCustom = new InstallmentsCCCustom();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\InstallmentsCCCustom::render
     *
     * @return void
     */
    public function testRender_simple(): void
    {
        try {
            $this->installmentsCCCustom->setPaymentData($this->normalPaymentParams);
            $this->installmentsCCCustom->setCallbackUrl('https://test.com');

            $this->assertIsString($this->installmentsCCCustom->render([]));
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\InstallmentsCCCustom::render
     *
     * @return void
     */
    public function testRender_noAvailablePlans(): void
    {
        try {
            $this->installmentsCCCustom->setPaymentData($this->normalPaymentParams);
            $this->installmentsCCCustom->setCallbackUrl('https://test.com');

            $plansList = [
                'response_code'       => 12345,
                'amount'              => 100000,
                'response_message'    => 'Success',
                'signature'           => 'test_signature',
                'merchant_identifier' => '12345',
                'access_code'         => '12345',
                'query_command'       => 'GET_INSTALLMENTS_PLANS',
                'language'            => 'en',
                'currency'            => 'USD',
                'installment_detail'  => [
                    'issuer_detail' => [
                        [
                            'bins'                       => [
                                [
                                    'country_code'    => 'JOR',
                                    'bin'             => 123456,
                                    'card_brand_code' => 'VISA',
                                    'currency_code'   => 'USD',
                                ],
                            ],
                            'confirmation_message_ar'    => 'Issuer 1 confirmation message in AR',
                            'processing_fees_message_ar' => 'abcd',
                            'issuer_logo_en'             => 'logo.png',
                            'issuer_code'                => 'abcd',
                            'processing_fees_message_en' => 'Processing Fees Message',
                            'issuer_logo_ar'             => 'logo.png',
                            'terms_and_condition_en'     => 'terms_url_en',
                            'banking_system'             => 'Non Islamic',
                            'country_code'               => 'JOR',
                            'issuer_name_ar'             => 'abcd',
                            'disclaimer_message_en'      => 'The Bank will convert your transaction within 2 days',
                            'terms_and_condition_ar'     => 'terms_url_ar',
                            'confirmation_message_en'    => 'Issuer 1 confirmation message in EN',
                            'issuer_name_en'             => 'Issuer1',
                            'plan_details'               => [],
                            'disclaimer_message_ar'      => 'The Bank will convert your transaction within 2 days',
                        ],
                    ],
                    'status'        => 123,
                ]
            ];

            $html = $this->installmentsCCCustom->render([
                'installment_plans_list' => $plansList,
            ]);

            $this->assertIsString($html);

            $regexes = [
                '/Total amount: \d+ \w{3}/i',
                '/<div class="installment_plans">/i',
            ];

            foreach ($regexes as $regex) {
                $this->assertMatchesRegularExpression($regex, $html);
            }

        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\InstallmentsCCCustom::render
     *
     * @return void
     */
    public function testRender(): void
    {
        try {
            $this->installmentsCCCustom->setPaymentData($this->normalPaymentParams);
            $this->installmentsCCCustom->setCallbackUrl('https://test.com');

            $plansList = [
                'response_code'       => 12345,
                'amount'              => 100000,
                'response_message'    => 'Success',
                'signature'           => 'test_signature',
                'merchant_identifier' => '12345',
                'access_code'         => '12345',
                'query_command'       => 'GET_INSTALLMENTS_PLANS',
                'language'            => 'en',
                'currency'            => 'USD',
                'installment_detail'  => [
                    'issuer_detail' => [
                        [
                            'bins'                       => [
                                [
                                    'country_code'    => 'JOR',
                                    'bin'             => 123456,
                                    'card_brand_code' => 'VISA',
                                    'currency_code'   => 'USD',
                                ],
                            ],
                            'confirmation_message_ar'    => 'Issuer 1 confirmation message in AR',
                            'processing_fees_message_ar' => 'abcd',
                            'issuer_logo_en'             => 'logo.png',
                            'issuer_code'                => 'abcd',
                            'processing_fees_message_en' => 'Processing Fees Message',
                            'issuer_logo_ar'             => 'logo.png',
                            'terms_and_condition_en'     => 'terms_url_en',
                            'banking_system'             => 'Non Islamic',
                            'country_code'               => 'JOR',
                            'issuer_name_ar'             => 'abcd',
                            'disclaimer_message_en'      => 'The Bank will convert your transaction within 2 days',
                            'terms_and_condition_ar'     => 'terms_url_ar',
                            'confirmation_message_en'    => 'Issuer 1 confirmation message in EN',
                            'issuer_name_en'             => 'Issuer1',
                            'plan_details'               => [
                                [
                                    'fees_amount'            => 200,
                                    'plan_type'              => 'Cross-Border',
                                    'amountPerMonth'         => 17000.00,
                                    'plan_code'              => 'abcd11',
                                    'maximum_amount'         => 10000000,
                                    'minimum_amount'         => 50000,
                                    'currency_code'          => 'USD',
                                    'rate_type'              => 'Flat',
                                    'fees_type'              => 'Percentage',
                                    'number_of_installment'  => 6,
                                    'processing_fees_type'   => 'Percentage',
                                    'processing_fees_amount' => 300,
                                    'fee_display_value'      => 400,
                                    'plan_merchant_type'     => 'Non Partner11',
                                ],
                                [
                                    'fees_amount'            => 200,
                                    'plan_type'              => 'Cross-Border',
                                    'amountPerMonth'         => 17000.00,
                                    'plan_code'              => 'abcd12',
                                    'maximum_amount'         => 10000000,
                                    'minimum_amount'         => 50000,
                                    'currency_code'          => 'USD',
                                    'rate_type'              => 'Flat',
                                    'fees_type'              => 'Percentage',
                                    'number_of_installment'  => 6,
                                    'processing_fees_type'   => 'Percentage',
                                    'processing_fees_amount' => 300,
                                    'fee_display_value'      => 400,
                                    'plan_merchant_type'     => 'Non Partner12',
                                ]
                            ],
                            'disclaimer_message_ar'      => 'The Bank will convert your transaction within 2 days',
                        ],
                        [
                            'bins'                       => [
                                [
                                    'country_code'    => 'JOR2',
                                    'bin'             => 123456,
                                    'card_brand_code' => 'VISA2',
                                    'currency_code'   => 'USD',
                                ],
                            ],
                            'confirmation_message_ar'    => 'Issuer 2 confirmation message in AR',
                            'processing_fees_message_ar' => 'abcd2',
                            'issuer_logo_en'             => 'logo.png',
                            'issuer_code'                => 'abcd2',
                            'processing_fees_message_en' => 'Processing Fees Message',
                            'issuer_logo_ar'             => 'logo2.png',
                            'terms_and_condition_en'     => 'terms_url2_en',
                            'banking_system'             => 'Non Islamic',
                            'country_code'               => 'JOR',
                            'issuer_name_ar'             => 'abcd2',
                            'disclaimer_message_en'      => 'The Bank will convert your transaction within 2 days',
                            'terms_and_condition_ar'     => 'terms_url2_ar',
                            'confirmation_message_en'    => 'Issuer 2 confirmation message in EN',
                            'issuer_name_en'             => 'Issuer2',
                            'plan_details'               => [
                                [
                                    'fees_amount'            => 200,
                                    'plan_type'              => 'Cross-Border',
                                    'amountPerMonth'         => 17000.00,
                                    'plan_code'              => 'abcd21',
                                    'maximum_amount'         => 10000000,
                                    'minimum_amount'         => 50000,
                                    'currency_code'          => 'USD',
                                    'rate_type'              => 'Flat',
                                    'fees_type'              => 'Percentage',
                                    'number_of_installment'  => 6,
                                    'processing_fees_type'   => 'Percentage',
                                    'processing_fees_amount' => 300,
                                    'fee_display_value'      => 400,
                                    'plan_merchant_type'     => 'Non Partner21',
                                ],
                                [
                                    'fees_amount'            => 200,
                                    'plan_type'              => 'Cross-Border',
                                    'amountPerMonth'         => 17000.00,
                                    'plan_code'              => 'abcd22',
                                    'maximum_amount'         => 10000000,
                                    'minimum_amount'         => 50000,
                                    'currency_code'          => 'USD',
                                    'rate_type'              => 'Flat',
                                    'fees_type'              => 'Percentage',
                                    'number_of_installment'  => 6,
                                    'processing_fees_type'   => 'Percentage',
                                    'processing_fees_amount' => 300,
                                    'fee_display_value'      => 400,
                                    'plan_merchant_type'     => 'Non Partner22',
                                ]
                            ],
                            'disclaimer_message_ar'      => 'The Bank will convert your transaction within 2 days',
                        ],
                    ],
                    'status'        => 123,
                ]
            ];

            $html = $this->installmentsCCCustom->render([
                'installment_plans_list' => $plansList,
            ]);

            $this->assertIsString($html);

            $issuerDetails = $plansList['installment_detail']['issuer_detail'];

            $regexes = [
                '/Total amount: \d+ \w{3}/i',
                '/<div class="installment_plans">/i',
                '/<fieldset class="installment_plan">/i',

                '/'. $issuerDetails[0]['confirmation_message_' . $plansList['language'] ] . '/i',
                '/'. $issuerDetails[1]['confirmation_message_' . $plansList['language'] ] . '/i',

                '/href="'. $issuerDetails[0]['terms_and_condition_' . $plansList['language'] ] . '"/i',
                '/href="'. $issuerDetails[1]['terms_and_condition_' . $plansList['language'] ] . '"/i',

                '/<div.+data-plan-code=\'' .
                $issuerDetails[0]['plan_details'][0]['plan_code']
                . '\' data-issuer-code=\'' .
                $issuerDetails[0]['issuer_code']
                . '\'>/i',

                '/<div.+data-plan-code=\'' .
                $issuerDetails[0]['plan_details'][1]['plan_code']
                . '\' data-issuer-code=\'' .
                $issuerDetails[0]['issuer_code']
                . '\'>/i',

                '/<div.+data-plan-code=\'' .
                $issuerDetails[1]['plan_details'][0]['plan_code']
                . '\' data-issuer-code=\'' .
                $issuerDetails[1]['issuer_code']
                . '\'>/i',

                '/<div.+data-plan-code=\'' .
                $issuerDetails[1]['plan_details'][1]['plan_code']
                . '\' data-issuer-code=\'' .
                $issuerDetails[1]['issuer_code']
                . '\'>/i',
            ];

            foreach ($regexes as $regex) {
                $this->assertMatchesRegularExpression($regex, $html);
            }

        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
