<?php

namespace Tests\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomTokenization;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCCustom;
use Tests\APSTestCase;

/**
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSSignature
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSValidator
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomAuthorization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\CCCustomTokenization
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter
 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCCustom
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO
 * @covers \AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n
 */
class CCCustomTest extends APSTestCase {

	private CCCustom $creditCardCustom;

	public function setUp(): void
	{
		parent::setUp();

		try {
			APSMerchant::setMerchantParams($this->merchantConfig);
			$this->creditCardCustom = new CCCustom();
		} catch (APSException $e) {
			$this->fail();
		}
	}

	/**
	 * @test
	 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCCustom::render
	 *
	 * @return void
	 */
	public function testRender(): void
	{
		try {
			$paymentData = $this->normalPaymentParams;
			$paymentData['expiry_date'] = '1234';
			$paymentData['card_number'] = '123456789012345';
			$paymentData['card_security_code'] = '123';
			$this->creditCardCustom->setPaymentData($paymentData);
			$this->creditCardCustom->setCallbackUrl('https://test.com');

			$this->assertIsString($this->creditCardCustom->render([]));
		} catch (APSException $e) {
			$this->fail($e->getMessage());
		}
	}

	/**
	 * @test
	 * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCCustom::render
	 *
	 * @return void
	 * @throws APSException
	 */
	public function testRender_html() {
		$paymentData = $this->normalPaymentParams;
		$paymentData['expiry_date'] = '1234';
		$paymentData['card_number'] = '123456789012345';
		$paymentData['card_security_code'] = '123';

		$this->creditCardCustom->setPaymentData($paymentData);
		$this->creditCardCustom->setCallbackUrl('https://test.com');

        $paymentTypeAdapter = new CCCustomTokenization();

		$html = $this->creditCardCustom->render();

		$regexes = [
			'/<form id="custom_checkout_form"/i',
			'/action="' . str_replace('/','\/', $paymentTypeAdapter->getEndpoint()) . '"/i',
			'/type="hidden" name="access_code" value="' . $this->merchantConfig['access_code'] . '"/i',
			'/type="hidden" name="merchant_identifier" value="' . $this->merchantConfig['merchant_identifier'] . '"/i',
			'/type="hidden" name="service_command" value="TOKENIZATION" \/>/i',
			'/type="hidden" name="merchant_reference" value="' . $paymentData['merchant_reference'] . '"/i',
			'/type="hidden" name="language" value="' . $paymentData['language'] . '"/i',
			'/type="text" name="card_number"/i',
			'/type="text" name="expiry_date"/i',
			'/type="text" name="card_security_code"/i',
			'/type="text" name="card_holder_name"/i',
			'/<button type="submit" class="">Place order<\/button>/i',
		];

		foreach ($regexes as $regex) {
			$this->assertMatchesRegularExpression($regex, $html);
		}
	}


    /**
     * @test
     * @covers \AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCCustom::getCallbackUrlAddon
     *
     * @return void
     */
    public function testGetCallbackUrlAddon(): void
    {
        try {
            $creditCardCustom = new CCCustom();
            $creditCardCustom->useAuthorizationCommand();

            $authorizationCallBackAddon = $creditCardCustom->getCallbackUrlAddon();

            $creditCardCustom->usePurchaseCommand();

            $this->assertNotEquals(
                $authorizationCallBackAddon,
                $creditCardCustom->getCallbackUrlAddon()
            );
        } catch (APSException $e) {
            $this->fail($e->getMessage());
        }
    }
}
