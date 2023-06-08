<?php

namespace AmazonPaymentServicesSdk\FrontEndAdapter\Payments;

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCCustomPurchase;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\Payment\InstallmentsCCCustomTokenization;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentDTO;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Model\PaymentTypeAdapter;
use AmazonPaymentServicesSdk\FrontEndAdapter\Core\FrontEndAdapter;

class InstallmentsCCCustom extends FrontEndAdapter
{
	protected ?PaymentTypeAdapter $apsModelObject;

	/**
	 * @throws APSException
	 */
	public function __construct()
	{
		$this->templateFilePath = __DIR__ . '/../Templates/installmentsCustom.html';

		// set up this payment type
		$this->apsModelObject = new InstallmentsCCCustomTokenization();

		parent::__construct();
	}

	public function render(array $options = null): string
	{
		if (!($options['button_text'] ?? null)) {
			$options['button_text'] = 'Place order';
		}

        if (isset($options['installment_plans_list']) && is_array($options['installment_plans_list'])) {
            $installmentPlanTemplatePath = str_replace(
                'installmentsCustom', 'installmentPlan', $this->templateFilePath
            );
            if (file_exists($installmentPlanTemplatePath)) {
                $installmentPlanTemplateContent = file_get_contents($installmentPlanTemplatePath);

                $installmentPlansHtml = '';

                $installmentsData = $this->prepareInstallmentsPlansDetails($options['installment_plans_list']);

                foreach ((array)$installmentsData['availablePlans'] as $planData) {
                    $installmentPlansHtml .= $this->findAndReplaceHtmlVariables($installmentPlanTemplateContent,
                        (array)$planData);
                }

                $options['total_amount'] = $installmentsData['totalAmount'];
                $options['installment_plans'] = $installmentPlansHtml;
            }
        }

		return parent::render($options);
	}

	public function getCallbackUrlAddon(): string {
		return '?discriminator=' . (new InstallmentsCCCustomPurchase())->getDiscriminator();
	}

    /**
     * @param $installmentsCallResult
     *
     * @return string[]
     */
    private function prepareInstallmentsPlansDetails($installmentsCallResult): array
    {
        $plansDetails   = [
            'totalAmount'       => '' . $this->convertDecAmount($installmentsCallResult['amount'],
                    $installmentsCallResult['currency']) . ' ' . $installmentsCallResult['currency'],
            'availablePlans'    => [],
        ];

        $allPlans      = array_filter(
            $installmentsCallResult['installment_detail']['issuer_detail'],
            fn($row) => !empty($row['plan_details'])
        );

        if (!empty($allPlans)) {
	        foreach ( $allPlans as $row ) {
		        foreach ( $row['plan_details'] as $plan ) {
			        $monthsText = 'Months';
			        $monthText  = 'month';

			        $plansHtml = "<div>";

			        $interest = $this->convertDecAmount( $plan['fee_display_value'],
				        $plan['currency_code'] );

			        $bankingSystem = $row['banking_system'];

			        $interestText = 'Non Islamic' === $bankingSystem ? 'Interest' : 'Profit Rate';

			        $interestInfo = $interest . ( 'Percentage' === $plan['fees_type'] ? '%' : '' ) . ' '
			                        . $interestText;

			        $plansHtml .= "<div>
							<div style='display: flex;' data-interest ='" . $interestInfo . "' data-amount='"
			                      . $plan['amountPerMonth'] . "' data-plan-code='"
			                      . $plan['plan_code'] . "' data-issuer-code='" . $row['issuer_code'] . "'>
								<input type='radio'>
								<p>" . $plan['number_of_installment'] . ' ' . $monthsText . "</p>
								<p><strong>" . $plan['amountPerMonth'] . '</strong> '
			                      . $plan['currency_code'] . '/' . $monthText . "</p>
								<p>" . $interest . ( 'Percentage' === $plan['fees_type'] ? '%' : '' )
			                      . ' ' . $interestText . '</p>
							</div>
						</div>
	                </div>';
			        //Plan info
			        $termsUrl          = $row[ 'terms_and_condition_' . $installmentsCallResult['language'] ];
			        $processingContent = $row[ 'processing_fees_message_' . $installmentsCallResult['language'] ];
			        $issuerText        = $row[ 'issuer_name_' . $installmentsCallResult['language'] ];
			        $issuerLogo        = $row[ 'issuer_logo_' . $installmentsCallResult['language'] ];

			        $termsText = 'I agree with the installment {terms_link} to proceed with the transaction';
			        $termsText = str_replace( '{terms_link}',
				        '<a target="_blank" href="' . $termsUrl . '">terms and condition</a>',
				        $termsText );
			        $planInfo  = '<input type="checkbox" name="installment_term" id="installment_term" required/>'
			                     . $termsText;
			        $planInfo  .= '<p> ' . $processingContent . '</p>';

			        $issuerInfo = '';

			        $plansDetails['availablePlans'][] = [
				        'plansHtml'      => $plansHtml,
				        'planInfo'       => $planInfo,
				        'issuerInfo'     => $issuerInfo,
				        'confirmationEn' => $row['confirmation_message_en'],
				        'confirmationAr' => $row['confirmation_message_ar'],
			        ];
		        }
	        }

            return [
                'totalAmount'       => $plansDetails['totalAmount'],
                'availablePlans'    => $plansDetails['availablePlans'],
            ];
        }

        return [
            'totalAmount' => $plansDetails['totalAmount']
        ];
    }

    /**
     * Convert Amount with decimal points
     *
     * @param float $amount
     * @param string  $currencyCode
     *
     * @return float
     */
    private function convertDecAmount(float $amount, string $currencyCode): float
    {
        $decimalPoints = PaymentDTO::getCurrencyDecimalPoints($currencyCode);

        $divideBy      = intval(str_pad(1, $decimalPoints + 1, 0, STR_PAD_RIGHT));

        $newAmount     = 0 === $decimalPoints ? $amount : $amount / $divideBy;

        return round( $newAmount, 2 );
    }
}
