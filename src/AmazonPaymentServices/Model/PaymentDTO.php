<?php

namespace AmazonPaymentServicesSdk\AmazonPaymentServices\Model;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Core\APSI18n;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSExceptionCodes;

class PaymentDTO
{
	protected array $paymentData;
	protected ?PaymentTypeAdapter $paymentTypeAdapter;

	private float $originalAmount;
	private float $payfortAmount;

    /**
     * @throws APSException
     */
    public function __construct(array $paymentData = null, PaymentTypeAdapter $paymentTypeAdapter = null)
	{
		$this->paymentData = $paymentData ?? [];

		$this->paymentTypeAdapter = $paymentTypeAdapter;

        if ($this->paymentTypeAdapter) {
	        $this->validate();
        }

		$this->setPaymentData($this->paymentData);
	}

	public function get(string $parameterName, bool $isMerchant = true): mixed
	{
		if ('amount' === $parameterName) {
			return $this->getAmount($isMerchant);
		}

		return $this->paymentData[$parameterName] ?? null;
	}

	public function set(string $parameter, mixed $value): self
	{
		$this->paymentData[$parameter] = $value;

		if ('amount' === $parameter) {
			$this->setAmount($this->paymentData['amount'], $this->paymentData['currency']);
		}

		return $this;
	}

	public function getPaymentData(bool $isMerchant = true): array
	{
		$returnData = $this->paymentData;
		$returnData['amount'] = $this->getAmount($isMerchant);

		return $returnData;
	}

	public function setPaymentData(array $paymentData): self
	{
		$this->paymentData = $paymentData;

		if (!empty($this->paymentData) && isset($this->paymentData['amount'])) {
			$this->setAmount($this->paymentData['amount'], $this->paymentData['currency']);
		}

		return $this;
	}

	public function getAmount(bool $isMerchant = true): float
	{
		return $isMerchant ? $this->originalAmount : $this->payfortAmount;
	}

	public function setAmount(float $amount, string $currency): self
	{
		$this->originalAmount = $amount;
		$this->payfortAmount = $this->convertAmountToInteger($amount, $currency);
		$this->paymentData['amount'] = $this->payfortAmount;

		return $this;
	}

	public function getPaymentTypeAdapter(): PaymentTypeAdapter
	{
		return $this->paymentTypeAdapter;
	}

    /**
     * @param PaymentTypeAdapter $paymentTypeAdapter
     *
     * @return $this
     *
     * @throws APSException
     */
	public function setPaymentTypeAdapter(PaymentTypeAdapter $paymentTypeAdapter): self
	{
		$this->paymentTypeAdapter = $paymentTypeAdapter;

        $this->validate();

        return $this;
	}

	/**
	 * @return bool
	 *
	 * @throws APSException
	 */
	public function validate(): bool
	{
		if ( ! $this->paymentTypeAdapter instanceof PaymentTypeAdapter ) {
			throw new APSException(
                APSI18n::getText('aps_payment_adapter_missing'),
				APSExceptionCodes::APS_PAYMENT_ADAPTER_MISSING,
			);
		}

		return $this->paymentTypeAdapter->isValid($this->paymentData);
	}

	/**
	 * Convert Amount to integer
	 *
	 * @param float  $amount
	 * @param string $currencyCode
	 *
	 * @return int
	 */
	private function convertAmountToInteger(float $amount, string $currencyCode): int
	{
		return  (int)($amount * pow(10, $this->getCurrencyDecimalPoints($currencyCode)));
	}

    /**
     * Convert Amount to integer
     *
     * @param int $amount
     * @param string $currencyCode
     *
     * @return float
     */
	public static function convertIntegerToAmount(int $amount, string $currencyCode): float
	{
        $decimalPlaces = self::getCurrencyDecimalPoints($currencyCode);
        if (!$decimalPlaces) {
            return (float)$amount;
        }

		return  (float)($amount / pow(10, $decimalPlaces));
	}

	/**
	 * Get Decimal place of currency
	 *
	 * @param string $currency
	 *
	 * @return int
	 */
	public static function getCurrencyDecimalPoints(string $currency): int
	{
		$decimalPoint  = 2;
		$arrCurrencies = array(
			'JOD' => 3,
			'KWD' => 3,
			'OMR' => 3,
			'TND' => 3,
			'BHD' => 3,
			'LYD' => 3,
			'IQD' => 3,
			'CLF' => 4,
			'BIF' => 0,
			'DJF' => 0,
			'GNF' => 0,
			'ISK' => 0,
			'JPY' => 0,
			'KMF' => 0,
			'KRW' => 0,
			'CLP' => 0,
			'PYG' => 0,
			'RWF' => 0,
			'UGX' => 0,
			'VND' => 0,
			'VUV' => 0,
			'XAF' => 0,
			'BYR' => 0,
		);

		if (isset($arrCurrencies[$currency])) {
			$decimalPoint = $arrCurrencies[$currency];
		}

		return $decimalPoint;
	}
}
