<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Response;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCCustomPurchase;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter;

class InstallmentsCCCustomResponse extends ResponseAdapter
{
	public function getSuccessStatusCodes(): array
	{
		return [
			'18', '02', '14', '44', '20',
		];
	}

	public function getPaymentOptions(): array
	{
		return [
			'payment_type'      => APSConstants::PAYMENT_TYPE_INSTALMENTS,
			'integration_type'  => APSConstants::INTEGRATION_TYPE_CUSTOM,
		];
	}

	/**
	 * At Installments Credit Card Standard checkout, we have two actions:
	 * - tokenize
	 * - purchase
	 * If we are at the tokenization response, then ask for purchase,
	 * otherwise do nothing
	 *
	 * @return string|null|APSResponse
	 *
	 * @throws APSException
	 */
	public function process(): null|string|APSResponse
	{
		if (!$this->paymentResponseModel->isSuccess()) {
			// if the transaction failed, don't do further actions
			return null;
		}

		$this->handleInstallmentsPurchaseResponse();

		// ask for the authorization/purchase (if this is a tokenization response)
		$authorizationResult = $this->handleAuthorization(new InstallmentsCCCustomPurchase());
		if (null !== $authorizationResult) {
			$this->setResponseData($authorizationResult);
			return $this->process();
		}

		// redirect / show modal for Secure 3DS verification (if this is needed)
		return $this->handleSecure3ds();
	}

	private function handleInstallmentsPurchaseResponse(): void
	{
		if(!session_id())
		{
			session_start();
		}

        $this->authorizationPaymentData['plan_code'] =
            $_SESSION['plan_code'] ?? ($this->authorizationPaymentData['plan_code'] ?? null);
        $this->authorizationPaymentData['issuer_code'] =
            $_SESSION['issuer_code'] ?? ($this->authorizationPaymentData['issuer_code'] ?? null);
	}

	/**
	 * @return string
	 */
	public function getDiscriminator(): string {
		return (new InstallmentsCCCustomPurchase())->getDiscriminator();
	}
}
