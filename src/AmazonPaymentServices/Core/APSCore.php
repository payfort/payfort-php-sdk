<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use GuzzleHttp\Exception\GuzzleException;

class APSCore
{
    private ?array $merchantParams = null;

    private ?APSConnector $connector = null;
    private ?APSValidator $validator = null;

    /**
     * @throws APSException
     */
    public function __construct()
    {
        $this->merchantParams = APSMerchant::getMerchantParams();
    }

    /**
     * @return APSValidator
     */
    public function getValidator(): APSValidator
    {
        if (null === $this->validator) {
            $this->validator = new APSValidator();
        }

        return $this->validator;
    }

    /**
     * @return APSConnector
     */
    private function getConnector(): APSConnector
    {
        if (null === $this->connector) {
            $this->connector = new APSConnector();
        }

        return $this->connector;
    }

    /**
     * Calculate signature for a payment data list
     *
     * @param array $paymentParams
     *
     * @return string
     */
    public function calculateRequestSignature(array $paymentParams): string
    {
        return (new APSSignature())
            ->calculateSignature($paymentParams, true, $this->merchantParams);
    }

    /**
     * @throws APSException
     */
    public function isResponseValid(array $responseData): bool
    {
        return APSValidator::isResponseValid($this->merchantParams, $responseData);
    }

    /**
     * @param PaymentDTO $paymentDTO
     *
     * @return array
     *
     * @throws APSException
     */
    public function callToAuthorizePayment(PaymentDTO $paymentDTO): array
    {
        return $this->doCallToSpecifiedPayment($paymentDTO);
    }

	/**
	 * Capture a previously authorized payment
	 *
	 * @param PaymentDTO $paymentData
	 *
	 * @return array
	 *
	 * @throws APSException
	 */
    public function callToCapturePayment(PaymentDTO $paymentData): array
    {
	    return $this->doCallToSpecifiedPayment($paymentData);
    }

	/**
	 * * Refund a previously authorized payment
	 *
	 * @param PaymentDTO $paymentData
	 *
	 * @return array
	 *
	 * @throws APSException
	 */
    public function callToRefundPayment(PaymentDTO $paymentData): array
    {
	    return $this->doCallToSpecifiedPayment($paymentData);
    }

	/**
	 * Void a previously authorized payment
	 *
	 * @param PaymentDTO $paymentData
	 *
	 * @return array
	 *
	 * @throws APSException
	 */
    public function callToVoidPayment(PaymentDTO $paymentData): array
    {
	    return $this->doCallToSpecifiedPayment($paymentData);
    }

	/**
	 * Void a previously authorized payment
	 *
	 * @param PaymentDTO $paymentData
	 *
	 * @return array
	 *
	 * @throws APSException
	 */
    public function callToCheckPaymentStatus(PaymentDTO $paymentData): array
    {
	    return $this->doCallToSpecifiedPayment($paymentData);
    }

    /**
	 * * Recurring payment
	 *
	 * @param PaymentDTO $paymentData
	 *
	 * @return array
	 *
	 * @throws APSException
	 */
	public function callToRecurringPayment(PaymentDTO $paymentData): array
	{
		return $this->doCallToSpecifiedPayment($paymentData);
	}

    /**
     * * MOTO payment
     *
     * @param PaymentDTO $paymentData
     *
     * @return array
     *
     * @throws APSException
     */
    public function callToMotoPayment(PaymentDTO $paymentData): array
    {
        return $this->doCallToSpecifiedPayment($paymentData);
    }

    /**
     * * Trusted payment
     *
     * @param PaymentDTO $paymentData
     *
     * @return array
     *
     * @throws APSException
     */
    public function callToTrustedPayment(PaymentDTO $paymentData): array
    {
        return $this->doCallToSpecifiedPayment($paymentData);
    }

    /**
     * Get installments plans details and issuers configured
     *
     * @param PaymentDTO $paymentData
     *
     * @return array
     * @throws APSException
     */
    public function getInstallmentsPlans(PaymentDTO $paymentData): array
    {
        return $this->doCallToSpecifiedPayment($paymentData);
    }

    /**
     * Call to an apple-provided URL
     * to get the transaction session codes
     *
     * @param string $appleUrl
     * @param array $parameters
     * @param array $options
     *
     * @return array|string
     *
     * @throws APSException
     */
    public function getAppleCustomerSession(string $appleUrl, array $parameters, array $options): array|string
    {
        return $this->doCall($appleUrl, $parameters, $options, false, true);
    }

    /**
     * Call to finalize the AUTHORIZE transaction with APS
     *
     * @param PaymentDTO $paymentDTO
     *
     * @return array
     *
     * @throws APSException
     */
    public function callToAuthorizeApplePay(PaymentDTO $paymentDTO): array
    {
        return $this->doCallToSpecifiedPayment($paymentDTO);
    }

    /**
     * Call to finalize the PURCHASE transaction with APS
     *
     * @param PaymentDTO $paymentDTO
     *
     * @return array
     *
     * @throws APSException
     */
    public function callToPurchaseApplePay(PaymentDTO $paymentDTO): array
    {
        return $this->doCallToSpecifiedPayment($paymentDTO);
    }


    /**
     * @param PaymentDTO $paymentDto
     *
     * @return array
     *
     * @throws APSException
     */
	private function doCallToSpecifiedPayment(PaymentDTO $paymentDto): array
	{
        $paymentData = $paymentDto->getPaymentTypeAdapter()->generateParameters($paymentDto);

		$paymentData['signature'] = $this->calculateRequestSignature($paymentData);

		return $this->doCall($paymentDto->getPaymentTypeAdapter()->getEndpoint(), $paymentData);
	}

    /**
     * Do the call
     *
     * @param string $endpoint
     * @param array $apsPaymentParams
     * @param array|null $apsPaymentOptions
     * @param bool $validateSignature
     * @param bool $dontDecode
     *
     * @return array|string
     *
     * @throws APSException
     */
    private function doCall(
        string $endpoint,
        array $apsPaymentParams,
        array $apsPaymentOptions = null,
        bool $validateSignature = true,
        bool $dontDecode = false
    ): array|string
    {
        try {
            $serverResponse = $this
                ->getConnector()
                ->callToAps($endpoint, $apsPaymentParams, $apsPaymentOptions, $dontDecode);
        } catch (GuzzleException $e) {
            throw new APSException(
				APSI18n::getText('aps_s2s_call_failed') . $e->getMessage(),
	            APSExceptionCodes::APS_S2S_CALL_FAILED,
            );
        }

        if ($validateSignature && !$this->isResponseValid($serverResponse)) {
            throw new APSException(
                APSI18n::getText('aps_s2s_call_response_signature_failed'),
	            APSExceptionCodes::APS_S2S_CALL_RESPONSE_SIGNATURE_FAILED,
            );
        }

        return $serverResponse;
    }
}
