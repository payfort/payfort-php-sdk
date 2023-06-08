<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCStandardPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCStandardTokenization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter;

class InstallmentsCCStandard extends FrontEndAdapter
{
	protected ?PaymentTypeAdapter $apsModelObject;

	/**
	 * @throws APSException
	 */
	public function __construct()
	{
		$this->templateFilePath = __DIR__ . '/../Templates/standard.html';

		// set up this payment type
		$this->apsModelObject = new InstallmentsCCStandardTokenization();

		parent::__construct();
	}

	public function render(array $options = null): string
	{
		if (!($options['button_text'] ?? null)) {
			$options['button_text'] = 'Place order';
		}

		return parent::render($options);
	}

    public function getCallbackUrlAddon(): string
    {
        return '?discriminator=' . (new InstallmentsCCStandardPurchase())->getDiscriminator();
    }
}
