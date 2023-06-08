<?php

use AmazonPaymentServicesSdk\AmazonPaymentServices\Exceptions\APSException;
use AmazonPaymentServicesSdk\AmazonPaymentServices\Merchant\APSMerchant;
use AmazonPaymentServicesSdk\FrontEndAdapter\Maintenance\PaymentTrusted;
use AmazonPaymentServicesSdk\FrontEndAdapter\Payments\CCCustom;

require_once '_autoload.php';

$sampleAppConfig = include '_sample_app_config.php';

$merchantParams = include 'merchant_config.php';
APSMerchant::setMerchantParams($merchantParams);

$paymentData = include 'payment_data_config.php';

$activeFile = '/credit_card_trusted.php';
include '_header.php';

$action = filter_input(INPUT_POST, 'action') ?? null;

if ('payment_trusted' === $action) {
    $paymentData = [
        'merchant_reference'    => filter_input(INPUT_POST, 'merchant_reference') ?? '',
        'amount'                => filter_input(INPUT_POST, 'amount') ?? '',
        'currency'              => filter_input(INPUT_POST, 'currency') ?? '',
        'language'              => filter_input(INPUT_POST, 'language') ?? '',
        'customer_email'        => filter_input(INPUT_POST, 'customer_email') ?? '',
        'eci'                   => filter_input(INPUT_POST, 'eci') ?? '',
        'customer_ip'           => filter_input(INPUT_POST, 'customer_ip') ?? '',
    ];
    $tokenName = '';
    if (!empty(filter_input(INPUT_POST, 'token_name') ?? '')) {
        $paymentData['token_name'] = $tokenName = filter_input(INPUT_POST, 'token_name') ?? '';
    }

    $cardNumber = '';
    if (empty($tokenName)) {
        if (!empty(filter_input(INPUT_POST, 'card_number') ?? '')) {
            $paymentData['card_number'] = $cardNumber = filter_input(INPUT_POST, 'card_number') ?? '';
        }
        if (!empty(filter_input(INPUT_POST, 'expiry_date') ?? '')) {
            $paymentData['expiry_date'] = filter_input(INPUT_POST, 'expiry_date') ?? '';
        }
    }

    if (!empty(filter_input(INPUT_POST, 'card_security_code') ?? '')) {
        $paymentData['card_security_code'] = filter_input(INPUT_POST, 'card_security_code') ?? '';
    }

    if (!empty($tokenName) || !empty($cardNumber)) {
        try {
            $callResult = (new PaymentTrusted())->paymentTrusted($paymentData);
            if (is_string($callResult)) {
                // and it requests 3ds validation,
                // which is not accessible to the client

                // handle the response here
            } else {
                // handle the response here
            }
        } catch (APSException $e) {
            // do your thing here to handle this error
        }
    } else {
        // do your thing here to handle this error
    }
}
?>
<div>
    <h3>Payment option: Credit Card Trusted</h3>
    <div>Order number: <?php echo $paymentData['merchant_reference']; ?></div>
    <div>Amount (Fort): <?php echo $paymentData['amount']; ?></div>
    <br />
    <div>
        <fieldset>
            <legend>Trusted Payment</legend>
            <form action="credit_card_trusted.php" method="POST">
                <input type="hidden" name="action" value="payment_trusted" />
                <table>
                    <tr>
                        <td>Merchant reference:</td>
                        <td>
                            <input type="text" name="merchant_reference"
                                   value="<?php echo 'O-00001-'.rand(1000, 99999); ?>"
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
                            <input type="text" name="currency" value="AED" placeholder="currency" />
                        </td>
                    </tr>
                    <tr>
                        <td>Language (ISO):</td>
                        <td>
                            <input type="text" name="language" value="en" placeholder="language" />
                        </td>
                    </tr>
                    <tr>
                        <td>Customer email:</td>
                        <td>
                            <input type="text" name="customer_email" value="test@aps.com"
                                   placeholder="customer_email" />
                        </td>
                    </tr>
                    <tr>
                        <td>Token Name:</td>
                        <td>
                            <input type="text" name="token_name" id="token_name" value=""
                                   placeholder="token_name" onblur="handleStateChanged()" />
                            <span id="token_name_info">Remove CC data to add token name!</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Card Number</td>
                        <td>
                            <input type="text" name="card_number" id="card_number" placeholder="Card Number"
                                   maxlength="16" value="" onblur="handleStateChanged()" />
                            <span id="card_number_info">Remove token name data to add CC information!</span>
                        </td>
                    </tr>
                    <tr>
                        <td>Expiry Date</td>
                        <td>
                            <input type="text" name="expiry_date" id="expiry_date" placeholder="Expiry Date(YY/MM)"
                                   value="" onblur="handleStateChanged()" />

                        </td>
                    </tr>
                    <tr>
                        <td>CVV</td>
                        <td>
                            <input type="text" name="card_security_code" id="card_security_code" placeholder="CVV"
                                   maxlength="3" value="" onblur="handleStateChanged()" />
                        </td>
                    </tr>
                    <tr>
                        <td>Customer IP:</td>
                        <td>
                            <input type="text" name="customer_ip" value="127.0.0.1" placeholder="customer_ip" />
                        </td>
                    </tr>
                    <tr>
                        <td>ECI: </td>
                        <td>
                            <select name='eci'>
                                <option value='MOTO'>MOTO</option>
                                <option value='ECOMMERCE'>ECOMMERCE</option>
                                <option value='RECURRING'>RECURRING</option>
                            </select>
                        </td>
                    </tr>
                </table>
                <br />
                <input type="submit" value="Submit">
            </form>
        </fieldset>
    </div>
</div>
<script type="text/javascript">
    function handleStateChanged() {
        let tokenName = document.getElementById('token_name');
        let tokenNameInfo = document.getElementById('token_name_info');
        let cardNumber = document.getElementById('card_number');
        let cardNumberInfo = document.getElementById('card_number_info');
        let cardExpiryDate = document.getElementById('expiry_date');
        console.log('token name', tokenName, tokenName.value);

        if (tokenName.value) {
            cardNumber.value = '';
            cardNumber.setAttribute('disabled', 'disabled');
            cardExpiryDate.value = '';
            cardExpiryDate.setAttribute('disabled', 'disabled');

            cardNumberInfo.style.display = 'inline';
        } else {
            cardNumber.removeAttribute('disabled');
            cardExpiryDate.removeAttribute('disabled');

            cardNumberInfo.style.display = 'none';

            if (cardNumber.value) {
                tokenName.setAttribute('disabled', 'disabled');
                tokenNameInfo.style.display = 'inline';
            } else {
                tokenName.removeAttribute('disabled');
                tokenNameInfo.style.display = 'none';
            }
        }
    }

    handleStateChanged();
</script>
<?php
include '_footer.php';

