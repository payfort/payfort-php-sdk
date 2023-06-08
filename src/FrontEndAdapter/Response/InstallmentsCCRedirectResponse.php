<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Response;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCRedirectPurchase;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter;

class InstallmentsCCRedirectResponse extends ResponseAdapter
{
	public function getSuccessStatusCodes(): array
	{
		return [
			'02', '14', '44',
		];
	}

	public function getPaymentOptions(): array
	{
		return [
			'payment_type'      => APSConstants::PAYMENT_TYPE_INSTALMENTS,
			'integration_type'  => APSConstants::INTEGRATION_TYPE_REDIRECT,
		];
	}

	/**
	 * At Installments Credit Card Redirect, we have just one action:
	 * - purchase
	 * If we are at the process method, purchase was already made.
	 * We don't have to do anything here
	 *
	 * @return string|null|APSResponse
	 */
	public function process(): null|string|APSResponse
	{
		// for this method, we don't need to add more
		return null;
	}

    /**
     * @return string
     */
    public function getDiscriminator(): string
    {
        return (new InstallmentsCCRedirectPurchase())->getDiscriminator();
    }
}
