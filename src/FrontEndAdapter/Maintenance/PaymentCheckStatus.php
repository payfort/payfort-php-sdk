<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentCheckStatusModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter;

class PaymentCheckStatus extends MaintenanceAdapter
{
	/**
	 * @param array $paymentData
	 *
	 * @return array
	 *
	 * @throws APSException
	 */
	public function paymentCheckStatus(array $paymentData): array
    {
        return $this->processCheckPayment(
            new PaymentDTO($paymentData, new PaymentCheckStatusModel())
        );
    }
}
