<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\PaymentRecurringModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter;

class PaymentRecurring extends MaintenanceAdapter
{
	/**
	 * @param array $paymentData
	 *
	 * @return array
	 *
	 * @throws APSException
	 */
    public function paymentRecurring(array $paymentData): array
    {
        return $this->processRecurringPayment(
            new PaymentDTO($paymentData, new PaymentRecurringModel())
        );
    }
}
