<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentVoidAuthorizationModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter;

class PaymentVoidAuthorization extends MaintenanceAdapter
{
    /**
     * @param array $paymentData
     *
     * @return array
     *
     * @throws APSException
     */
    public function paymentVoid(array $paymentData): array
    {
        return $this->processVoidPayment(
            new PaymentDTO($paymentData, new PaymentVoidAuthorizationModel())
        );
    }
}
