<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Response;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\APSResponse;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCStandardPurchase;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\ResponseAdapter;

class InstallmentsCCStandardResponse extends ResponseAdapter
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
			'integration_type'  => APSConstants::INTEGRATION_TYPE_STANDARD,
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

		// add required params for the purchase
		$this->handleInstallmentsPurchaseResponse();

		// ask for the authorization/purchase (if this is a tokenization response)
		$authorizationResult = $this->handleAuthorization(new InstallmentsCCStandardPurchase());
		if (null !== $authorizationResult) {
			$this->setResponseData($authorizationResult);
			return $this->process();
		}

		// redirect / show modal for Secure 3DS verification (if this is needed)
		return $this->handleSecure3ds();
	}

	private function handleInstallmentsPurchaseResponse(): void
	{
		if (isset($this->responseData['plan_code'])) {
			$this->authorizationPaymentData['plan_code'] = $this->responseData['plan_code'];
		}

		if (isset($this->responseData['issuer_code'])) {
			$this->authorizationPaymentData['issuer_code'] = $this->responseData['issuer_code'];
		}
	}

    /**
     * @return string
     */
    public function getDiscriminator(): string
    {
        return (new InstallmentsCCStandardPurchase())->getDiscriminator();
    }
}
