<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSConstants;

class InstallmentsCCStandardTokenization extends CCStandardTokenization
{
	protected string $discriminator = APSConstants::PAYMENT_TYPE_INSTALMENTS . '_'
    . APSConstants::INTEGRATION_TYPE_STANDARD . '_'
    . APSConstants::PAYMENT_COMMAND_TOKENIZATION;

	protected string $paymentType = APSConstants::PAYMENT_TYPE_INSTALMENTS;

	public function __construct()
	{
        parent::__construct();

		$this->requiredParameters[] = 'installments';
		$this->requiredParameters[] = 'amount';
		$this->requiredParameters[] = 'currency';

		$this->optionalParameters[] = 'customer_country_code';
	}
}
