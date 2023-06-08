<?php

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\InstallmentsPlans;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentCapture;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentCheckStatus;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentMoto;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentRecurring;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentRefund;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentVoidAuthorization;

require_once '_autoload.php';

$merchantParams = include 'merchant_config.php';
APSMerchant::setMerchantParams($merchantParams);

$activeFile = '/maintenance.php';
include '_header.php';

$action = filter_input(INPUT_POST, 'action') ?? null;

if ($action) {
    ?>
    <div>
        <h3>Response data</h3>
        <?php
        switch ($action) {
            case 'payment_capture':
                $paymentData = [
                    'merchant_reference'    => filter_input(INPUT_POST, 'merchant_reference') ?? '',
                    'amount'                => filter_input(INPUT_POST, 'amount') ?? '',
                    'currency'              => filter_input(INPUT_POST, 'currency') ?? '',
                    'language'              => filter_input(INPUT_POST, 'language') ?? '',
                ];

                try {
                    $callResult = (new PaymentCapture())->paymentCapture($paymentData);
                    // handle the response here
                } catch (APSException $e) {
                    // do your thing here to handle this error
                }
                break;

            case 'payment_void':
                $paymentData = [
                    'merchant_reference'    => filter_input(INPUT_POST, 'merchant_reference') ?? '',
                    'language'              => filter_input(INPUT_POST, 'language') ?? '',
                ];

                try {
                    $callResult = (new PaymentVoidAuthorization())->paymentVoid($paymentData);
                    // handle the response here

                } catch (APSException $e) {
                    // do your thing here to handle this error
                }
                break;

            case 'payment_refund':
                $paymentData = [
                    'merchant_reference'    => filter_input(INPUT_POST, 'merchant_reference') ?? '',
                    'amount'                => filter_input(INPUT_POST, 'amount') ?? '',
                    'currency'              => filter_input(INPUT_POST, 'currency') ?? '',
                    'language'              => filter_input(INPUT_POST, 'language') ?? '',
                ];

                try {
                    $callResult = (new PaymentRefund())->paymentRefund($paymentData);
                    // handle the response here

                } catch (APSException $e) {
                    // do your thing here to handle this error
                }
                break;
	        case 'payment_recurring':
		        $paymentData = [
			        'merchant_reference'    => filter_input(INPUT_POST, 'merchant_reference') ?? '',
			        'amount'                => filter_input(INPUT_POST, 'amount') ?? '',
			        'currency'              => filter_input(INPUT_POST, 'currency') ?? '',
			        'language'              => filter_input(INPUT_POST, 'language') ?? '',
			        'token_name'            => filter_input(INPUT_POST, 'token_name') ?? '',
			        'customer_email'        => filter_input(INPUT_POST, 'customer_email') ?? '',
		        ];

		        try {
			        $callResult = (new PaymentRecurring())->paymentRecurring($paymentData);
                    // handle the response here

		        } catch (APSException $e) {
                    // do your thing here to handle this error
		        }
		        break;

            case 'payment_check_status':
                $paymentData = [
                    'merchant_reference'    => filter_input(INPUT_POST, 'merchant_reference') ?? '',
                    'language'              => filter_input(INPUT_POST, 'language') ?? '',
                ];

                try {
                    $callResult = (new PaymentCheckStatus())->paymentCheckStatus($paymentData);
                    // handle the response here

                } catch (APSException $e) {
                    // do your thing here to handle this error
                }
                break;

            case 'payment_moto':
                $paymentData = [
                    'merchant_reference'    => filter_input(INPUT_POST, 'merchant_reference') ?? '',
                    'amount'                => filter_input(INPUT_POST, 'amount') ?? '',
                    'currency'              => filter_input(INPUT_POST, 'currency') ?? '',
                    'language'              => filter_input(INPUT_POST, 'language') ?? '',
                    'customer_email'        => filter_input(INPUT_POST, 'customer_email') ?? '',
                    'eci'                   => filter_input(INPUT_POST, 'eci') ?? '',
                    'token_name'            => filter_input(INPUT_POST, 'token_name') ?? '',
                    'customer_ip'           => filter_input(INPUT_POST, 'customer_ip') ?? '',
                ];

                try {
                    $callResult = (new PaymentMoto())->paymentMoto($paymentData);
                    // handle the response here

                } catch (APSException $e) {
                    // do your thing here to handle this error
                }
                break;

            case 'get_instalment_plans':
                $paymentData = [];
                if (filter_input(INPUT_POST, 'amount') ?? '') {
                    $paymentData['amount'] = filter_input(INPUT_POST, 'amount');
                }
                if (filter_input(INPUT_POST, 'currency') ?? '') {
                    $paymentData['currency'] = filter_input(INPUT_POST, 'currency');
                }
                if (filter_input(INPUT_POST, 'language') ?? '') {
                    $paymentData['language'] = filter_input(INPUT_POST, 'language');
                }

                try {
                    $callResult = (new InstallmentsPlans())->getInstallmentsPlans($paymentData);
                    // handle the response here

                } catch (APSException $e) {
                    // do your thing here to handle this error
                }
                break;


            default:
                //do nothing
        }
        ?>
    </div>
<?php
}
?>
<h3>Maintenance operations</h3>
<fieldset>
    <legend>Capture Payment</legend>
    <form action="maintenance.php" method="POST">
        <input type="hidden" name="action" value="payment_capture" />
        <table>
            <tr>
                <td>Merchant reference:</td>
                <td>
                    <input type="text" name="merchant_reference" value="O-00001-74044"
                           placeholder="merchant_reference" />
                </td>
            </tr>
            <tr>
                <td>Amount (Fort value):</td>
                <td>
                    <input type="text" name="amount" value="3197" placeholder="amount" />
                </td>
            </tr>
            <tr>
                <td>Currency:</td>
                <td>
                    <input type="text" name="currency" value="USD" placeholder="currency" />
                </td>
            </tr>
            <tr>
                <td>Language (ISO):</td>
                <td>
                    <input type="text" name="language" value="en" placeholder="language" />
                </td>
            </tr>
        </table>
        <br />
        <input type="submit" value="Submit" />
    </form>
</fieldset>

<fieldset>
    <legend>Void Payment</legend>
    <form action="maintenance.php" method="POST">
        <input type="hidden" name="action" value="payment_void" />
        <table>
            <tr>
                <td>Merchant reference:</td>
                <td>
                    <input type="text" name="merchant_reference" value="O-00001-74044"
                           placeholder="merchant_reference" />
                </td>
            </tr>
            <tr>
                <td>Language (ISO):</td>
                <td>
                    <input type="text" name="language" value="en" placeholder="language" />
                </td>
            </tr>
        </table>
        <br />
        <input type="submit" value="Submit" />
    </form>
</fieldset>

<fieldset>
    <legend>Refund Payment</legend>
    <form action="maintenance.php" method="POST">
        <input type="hidden" name="action" value="payment_refund" />
        <table>
            <tr>
                <td>Merchant reference:</td>
                <td>
                    <input type="text" name="merchant_reference" value="O-00001-74044"
                           placeholder="merchant_reference" />
                </td>
            </tr>
            <tr>
                <td>Amount (Fort value):</td>
                <td>
                    <input type="text" name="amount" value="3197" placeholder="amount" />
                </td>
            </tr>
            <tr>
                <td>Currency:</td>
                <td>
                    <input type="text" name="currency" value="USD" placeholder="currency" />
                </td>
            </tr>
            <tr>
                <td>Language (ISO):</td>
                <td>
                    <input type="text" name="language" value="en" placeholder="language" />
                </td>
            </tr>
        </table>
        <br />
        <input type="submit" value="Submit" />
    </form>
</fieldset>

<fieldset>
    <legend>Recurring Payment</legend>
    <form action="maintenance.php" method="POST">
        <input type="hidden" name="action" value="payment_recurring" />
        <table>
            <tr>
                <td>Merchant reference:</td>
                <td>
                    <input type="text" name="merchant_reference" value="<?php echo 'O-00001-'.rand(1000, 99999); ?>"
                           placeholder="merchant_reference" />
                </td>
            </tr>
            <tr>
                <td>Amount (Fort value):</td>
                <td>
                    <input type="text" name="amount" value="3197" placeholder="amount" />
                </td>
            </tr>
            <tr>
                <td>Currency:</td>
                <td>
                    <input type="text" name="currency" value="USD" placeholder="currency" />
                </td>
            </tr>
            <tr>
                <td>Language (ISO):</td>
                <td>
                    <input type="text" name="language" value="en" placeholder="language" />
                </td>
            </tr>
            <tr>
                <td>Token name:</td>
                <td>
                    <input type="text" name="token_name" value="035cd5dec15c436fa787af4fec0a77f5"
                           placeholder="token_name" />
                </td>
            </tr>
            <tr>
                <td>Customer email:</td>
                <td>
                    <input type="text" name="customer_email" value="test@aps.com" placeholder="customer_email" />
                </td>
            </tr>
        </table>
        <br />
        <input type="submit" value="Submit" />
    </form>
</fieldset>

<fieldset>
    <legend>Check Payment Status</legend>
    <form action="maintenance.php" method="POST">
        <input type="hidden" name="action" value="payment_check_status" />
        <table>
            <tr>
                <td>Merchant reference:</td>
                <td>
                    <input type="text" name="merchant_reference" value="O-00001-74044" placeholder="merchant_reference" />
                </td>
            </tr>
            <tr>
                <td>Language (ISO):</td>
                <td>
                    <input type="text" name="language" value="en" placeholder="language" />
                </td>
            </tr>
        </table>
        <br />
        <input type="submit" value="Submit" />
    </form>
</fieldset>

<fieldset>
    <legend>MOTO Payment</legend>
    <form action="maintenance.php" method="POST">
        <input type="hidden" name="action" value="payment_moto" />
        <table>
            <tr>
                <td>Merchant reference:</td>
                <td>
                    <input type="text" name="merchant_reference" value="<?php echo 'O-00001-'.rand(1000, 99999); ?>"
                           placeholder="merchant_reference" />
                </td>
            </tr>
            <tr>
                <td>Amount (Fort value):</td>
                <td>
                    <input type="text" name="amount" value="3197" placeholder="amount" />
                </td>
            </tr>
            <tr>
                <td>Currency:</td>
                <td>
                    <input type="text" name="currency" value="USD" placeholder="currency" />
                </td>
            </tr>
            <tr>
                <td>Language (ISO):</td>
                <td>
                    <input type="text" name="language" value="en" placeholder="language" />
                </td>
            </tr>
            <tr>
                <td>Customer Email:</td>
                <td>
                    <input type="text" name="customer_email" value="test@aps.com" placeholder="customer_email">
                </td>
            </tr>
            <tr>
                <td>Token name:</td>
                <td>
                    <input type="text" name="token_name" value="5540cb3b9e6a40e38227ab9141e7342a"
                           placeholder="token_name" />
                </td>
            </tr>
            <tr>
                <td>Customer IP:</td>
                <td>
                    <input type="text" name="customer_ip" value="127.0.0.1" placeholder="customer_ip" />
                </td>
            </tr>
        </table>
        <br />
        <input type="submit" value="Submit" />
    </form>
</fieldset>

<fieldset>
    <legend>Get Instalment Plans</legend>
    <form action="maintenance.php" method="POST">
        <input type="hidden" name="action" value="get_instalment_plans" />
        <table>
            <tr>
                <td>Amount (Fort value):</td>
                <td>
                    <input type="text" name="amount" value="100000" placeholder="amount" />
                </td>
            </tr>
            <tr>
                <td>Currency:</td>
                <td>
                    <input type="text" name="currency" value="USD" placeholder="currency" />
                </td>
            </tr>
            <tr>
                <td>Language (ISO):</td>
                <td>
                    <input type="text" name="language" value="en" placeholder="language" />
                </td>
            </tr>
        </table>
        <br />
        <input type="submit" value="Submit" />
    </form>
</fieldset>

<?php
include '_footer.php';
?>
