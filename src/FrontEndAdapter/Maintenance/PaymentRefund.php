<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentRefundModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter;

class PaymentRefund extends MaintenanceAdapter
{
    /**
     * @param array $paymentData
     *
     * @return array
     *
     * @throws APSException
     */
    public function paymentRefund(array $paymentData): array
    {
        return $this->processRefundPayment(
            new PaymentDTO($paymentData, new PaymentRefundModel())
        );
    }
}
