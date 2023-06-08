<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Logger\Logger;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayAuthorization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\ApplePayPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter;

class PaymentApplePayAps extends MaintenanceAdapter
{
    private bool $isPurchase;

    public function __construct()
    {
        $this->isPurchase = true;

        parent::__construct();
    }

    /**
     * Call APS for Apple Pay Authorization
     *
     * @param array $paymentData
     *
     * @return array
     *
     * @throws APSException
     */
    public function applePayAuthorization(array $paymentData): array
    {
        $this->isPurchase = false;

        return $this->doCallApsWithApplePay($paymentData);
    }

    /**
     * Call APS for Apple Pay Purchase
     *
     * @param array $paymentData
     *
     * @return array
     *
     * @throws APSException
     */
    public function applePayPurchase(array $paymentData): array
    {
        $this->isPurchase = true;

        return $this->doCallApsWithApplePay($paymentData);
    }

    /**
     * @param array $paymentData
     *
     * @return array
     *
     * @throws APSException
     */
    private function doCallApsWithApplePay(array $paymentData): array
    {
        Logger::getInstance()->info('Apple Pay APS Purchase call build-up');

        $paymentDataPOST = $this->getInputData();

        $paymentData = $this->transformInputDataIntoPaymentData($paymentData, $paymentDataPOST);

        if ($this->isPurchase) {
            Logger::getInstance()->info('Calling APS with purchase command');

            return $this->processApplePayPurchase(
                new PaymentDTO($paymentData, new ApplePayPurchase())
            );
        }

        Logger::getInstance()->info('Calling APS with authorization command');

        return $this->processApplePayAuthorization(
            new PaymentDTO($paymentData, new ApplePayAuthorization())
        );
    }

    /**
     * Retrieve Safari data
     *
     * @return array
     */
    private function getInputData(): array
    {
        $paymentDataPOST = filter_input_array(INPUT_POST);
        if (empty($paymentDataPOST)) {
            $paymentDataString = file_get_contents('php://input');
            $paymentDataPOST = json_decode(
                filter_var($paymentDataString, FILTER_UNSAFE_RAW, FILTER_FLAG_NO_ENCODE_QUOTES),
                true
            );
        }

        if (APSMerchant::isDebugMode()) {
            Logger::getInstance()->debug(
                'Apple Pay: Safari data received for APS call ' . $paymentDataString
            );
        }

        if (null === $paymentDataPOST) {
            return [];
        }

        return $paymentDataPOST;
    }

    /**
     * Transform the input data into APS required format
     *
     * @param array $paymentData
     * @param array $paymentDataPOST
     *
     * @return array
     */
    private function transformInputDataIntoPaymentData(array $paymentData, array $paymentDataPOST): array
    {
        $paymentData['apple_data'] =
            $paymentDataPOST['data']['paymentData']['data'] ?? ($paymentData['apple_data'] ?? '');
        $paymentData['apple_signature'] =
            $paymentDataPOST['data']['paymentData']['signature'] ?? ($paymentData['apple_signature'] ?? '');

        if (!isset($paymentData['apple_header']) || !is_array($paymentData['apple_header'])) {
            $paymentData['apple_header'] = [];
        }
        $paymentData['apple_header']['apple_transactionId'] =
            $paymentDataPOST['data']['paymentData']['header']['transactionId'] ?? ($paymentData['apple_header']['apple_transactionId'] ?? '');
        $paymentData['apple_header']['apple_publicKeyHash'] =
            $paymentDataPOST['data']['paymentData']['header']['publicKeyHash'] ?? ($paymentData['apple_header']['apple_publicKeyHash'] ?? '');
        $paymentData['apple_header']['apple_ephemeralPublicKey'] =
            $paymentDataPOST['data']['paymentData']['header']['ephemeralPublicKey'] ?? ($paymentData['apple_header']['apple_ephemeralPublicKey'] ?? '');

        if (!isset($paymentData['apple_paymentMethod']) || !is_array($paymentData['apple_paymentMethod'])) {
            $paymentData['apple_paymentMethod'] = [];
        }
        $paymentData['apple_paymentMethod']['apple_displayName'] =
            $paymentDataPOST['data']['paymentMethod']['displayName'] ?? ($paymentData['apple_paymentMethod']['apple_displayName'] ?? '');
        $paymentData['apple_paymentMethod']['apple_network'] =
            $paymentDataPOST['data']['paymentMethod']['network'] ?? ($paymentData['apple_paymentMethod']['apple_network'] ?? '');
        $paymentData['apple_paymentMethod']['apple_type'] =
            $paymentDataPOST['data']['paymentMethod']['type'] ?? ($paymentData['apple_paymentMethod']['apple_type'] ?? '');

        if (APSMerchant::isDebugMode()) {
            Logger::getInstance()->debug(
                'Apple Pay: Payment data after input transformation ' . json_encode($paymentData)
            );
        }

        return $paymentData;
    }
}
