<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentMotoModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter;

class PaymentMoto extends MaintenanceAdapter
{
    /**
     * @param array $paymentData
     *
     * @return array
     *
     * @throws APSException
     */

    public function paymentMoto(array $paymentData): array
    {
        return $this->processMotoPayment(
            new PaymentDTO($paymentData, new PaymentMotoModel())
        );
    }
}