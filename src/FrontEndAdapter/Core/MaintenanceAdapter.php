<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Core;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSCore;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;

abstract class MaintenanceAdapter
{
    protected APSCore $amazonPaymentServicesCore;

    public function __construct()
    {
        $this->amazonPaymentServicesCore = new APSCore();
    }

    /**
     * @return APSCore
     */
    protected function getApsCore(): APSCore
    {
        return $this->amazonPaymentServicesCore;
    }

    /**
     * @param PaymentDTO $paymentDTO
     * @return array
     *
     * @throws APSException
     */
    protected function getInstallmentPlans(PaymentDTO $paymentDTO): array
    {
        return $this->amazonPaymentServicesCore->getInstallmentsPlans($paymentDTO);
    }

    /**
     * @param PaymentDTO $paymentDTO
     * @return array
     *
     * @throws APSException
     */
    protected function processCapturePayment(PaymentDTO $paymentDTO): array
    {
        return $this->amazonPaymentServicesCore->callToCapturePayment($paymentDTO);
    }

    /**
     * @param PaymentDTO $paymentDTO
     * @return array
     *
     * @throws APSException
     */
    protected function processMotoPayment(PaymentDTO $paymentDTO): array
    {
        return $this->amazonPaymentServicesCore->callToMotoPayment($paymentDTO);
    }

    /**
     * @param PaymentDTO $paymentDTO
     * @return array
     *
     * @throws APSException
     */
    protected function processRecurringPayment(PaymentDTO $paymentDTO): array
    {
        return $this->amazonPaymentServicesCore->callToRecurringPayment($paymentDTO);
    }

    /**
     * @param PaymentDTO $paymentDTO
     * @return array
     *
     * @throws APSException
     */
    protected function processRefundPayment(PaymentDTO $paymentDTO): array
    {
        return $this->amazonPaymentServicesCore->callToRefundPayment($paymentDTO);
    }

    /**
     * @param PaymentDTO $paymentDTO
     * @return array
     *
     * @throws APSException
     */
    protected function processTrustedPayment(PaymentDTO $paymentDTO): array
    {
        return $this->amazonPaymentServicesCore->callToTrustedPayment($paymentDTO);
    }

    /**
     * @param PaymentDTO $paymentDTO
     * @return array
     *
     * @throws APSException
     */
    protected function processVoidPayment(PaymentDTO $paymentDTO): array
    {
        return $this->amazonPaymentServicesCore->callToVoidPayment($paymentDTO);
    }

    /**
     * @param PaymentDTO $paymentDTO
     * @return array
     *
     * @throws APSException
     */
    protected function processCheckPayment(PaymentDTO $paymentDTO): array
    {
        return $this->amazonPaymentServicesCore->callToCheckPaymentStatus($paymentDTO);
    }

    /**
     * @param PaymentDTO $paymentDTO
     * @return array
     *
     * @throws APSException
     */
    protected function processApplePayAuthorization(PaymentDTO $paymentDTO): array
    {
        return $this->amazonPaymentServicesCore->callToAuthorizeApplePay($paymentDTO);
    }

    /**
     * @param PaymentDTO $paymentDTO
     * @return array
     *
     * @throws APSException
     */
    protected function processApplePayPurchase(PaymentDTO $paymentDTO): array
    {
        return $this->amazonPaymentServicesCore->callToPurchaseApplePay($paymentDTO);
    }
}
