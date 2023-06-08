<?php

namespace Tests\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardTokenization;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCStandard;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCStandard
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\APSLogger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardTokenization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConnector
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCStandardAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class CCStandardTest extends APSTestCase
{
    private CCStandard $creditCardStandard;

    public function setUp(): void
    {
        parent::setUp();

        try {
            APSMerchant::setMerchantParams($this->merchantConfig);
            $this->creditCardStandard = new CCStandard();
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCStandard::render
     *
     * @return void
     */
    public function testRender_simple(): void
    {
        try {
            $this->creditCardStandard->setPaymentData($this->normalPaymentParams);
            $this->creditCardStandard->setCallbackUrl('https://test.com');

            $this->assertIsString($this->creditCardStandard->render([]));
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }

	/**
	 * @test
	 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCStandard::render
	 *
	 * @return void
	 * @throws APSException
	 */
    public function testRender_html() {
	    $this->creditCardStandard->setPaymentData($this->normalPaymentParams);
	    $this->creditCardStandard->setCallbackUrl('https://test.com');

        $paymentTypeAdapter = new CCStandardTokenization();

        $html = $this->creditCardStandard->render();

        $regexes = [
            '/<form name="aps_form"/i',
            '/action="' . str_replace('/','\/', $paymentTypeAdapter->getEndpoint()) . '"/i',
            '/target="standard_checkout_iframe"/i',
            '/onsubmit="displayMainIframe\(\)"/i',
            '/type="hidden" name="access_code" value="' . $this->merchantConfig['access_code'] . '"/i',
            '/type="hidden" name="merchant_identifier" value="' . $this->merchantConfig['merchant_identifier'] . '"/i',
            '/type="hidden" name="service_command" value="TOKENIZATION" \/>/i',
            '/type="hidden" name="merchant_reference" value="' . $this->normalPaymentParams['merchant_reference'] . '"/i',
            '/type="hidden" name="language" value="' . $this->normalPaymentParams['language'] . '"/i',
            '/<button type="submit" class="">Place order<\/button>/i',
            '/<iframe name="standard_checkout_iframe" class="aps_iframe " sandbox="allow-scripts allow-top-navigation allow-forms allow-same-origin"><\/iframe>/i',
            '/<div id="standard_redirect_message" style="display: none;"><\/div>/i',
            '/function displayMainIframe\(\) {/i',
        ];

        foreach ($regexes as $regex) {
            $this->assertMatchesRegularExpression($regex, $html);
        }
    }

    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCStandard::getCallbackUrlAddon
     *
     * @return void
     */
    public function testGetCallbackUrlAddon(): void
    {
        try {
            $creditCardStandard = new CCStandard();
            $creditCardStandard->useAuthorizationCommand();

            $authorizationCallBackAddon = $creditCardStandard->getCallbackUrlAddon();

            $creditCardStandard->usePurchaseCommand();

            $this->assertNotEquals(
                $authorizationCallBackAddon,
                $creditCardStandard->getCallbackUrlAddon()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
