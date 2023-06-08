<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Maintenance\InstallmentsPlansModel;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\MaintenanceAdapter;

class InstallmentsPlans extends MaintenanceAdapter
{
	/**
	 * @param array $paymentData
	 *
	 * @return array
	 *
	 * @throws APSException
	 */
	public function getInstallmentsPlans(array $paymentData): array
    {
        return $this->getInstallmentPlans(
            new PaymentDTO($paymentData, new InstallmentsPlansModel())
        );
    }
}
