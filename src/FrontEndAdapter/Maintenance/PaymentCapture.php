<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCaptureModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter;


class PaymentCapture extends MaintenanceAdapter
{

    /**
     * @param array $paymentData
     *
     * @return array
     *
     * @throws APSException
     */
    public function paymentCapture(array $paymentData): array
    {
        return $this->processCapturePayment(
            new PaymentDTO($paymentData, new PaymentCaptureModel())
        );
    }
}