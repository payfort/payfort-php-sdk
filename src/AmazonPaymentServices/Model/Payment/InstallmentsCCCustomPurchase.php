<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;

class InstallmentsCCCustomPurchase extends CCCustomPurchase
{
	protected string $discriminator = APSConstants::PAYMENT_TYPE_INSTALMENTS . '_'
    . APSConstants::INTEGRATION_TYPE_CUSTOM . '_'
    . APSConstants::PAYMENT_COMMAND_PURCHASE;

	protected string $paymentType = APSConstants::PAYMENT_TYPE_INSTALMENTS;
	protected string $command = APSConstants::PAYMENT_COMMAND_PURCHASE;

	public function __construct()
	{
        parent::__construct();

        $this->requiredParameters[] = 'installments';
        $this->requiredParameters[] = 'plan_code';
        $this->requiredParameters[] = 'issuer_code';
	}
}
