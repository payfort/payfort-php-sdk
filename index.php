<?php include('header.php') ?>
<?php
require_once 'PayfortIntegration.php';
$objFort = new PayfortIntegration();
$amount =  $objFort->amount;
$currency = $objFort->currency;
$totalAmount = $amount;
?>
    <section class="nav">
        <ul>
            <li class="active lead"> Payment Method</li>
            <li class="lead"> Pay</li>
            <li class="lead"> Done</li>
        </ul>
    </section>

    <section class="order-info">
        <ul class="items">
            <span>
                <i class="icon icon-bag"></i>
                <label class="lead" for="">Your Order</label>
            </span>
            <li><?php echo $objFort->itemName ?></li>
            <!-- <li>Lorem ipsum dolor sit amet, consectetur adipisicing elit. A ex magni delectus aliquam debitis</li> -->
        </ul>
        <ul>
            <li>
                <div class="v-seperator"></div>
            </li>
        </ul>
        <ul class="price">
            <span>
                <i class="icon icon-tag"></i>
                <label class="lead" for="">price</label>
            </span>

            <li><span class="curreny">$</span> <?php echo sprintf("%.2f",$totalAmount);?>	</li>
        </ul>
    </section>

    <div class="h-seperator"></div>

    <section class="payment-method">
        <label class="lead" for="">
            Choose a Payment Method <small>(click one of the options below)</small>
        </label>
        <ul>
            <li>
                <input id="po_creditcard" type="radio" name="payment_option" value="creditcard" checked="checked" style="display: none">
                <label class="payment-option active" for="po_creditcard">
                    <img src="assets/img/cc.png" alt="">
                    <span class="name">Pay with credit cards (Redirection)</span>
                    <em class="seperator hidden"></em>
                    <div class="demo-container hidden"> <!--  Area for the iframe section -->
                        <iframe src="" frameborder="0"></iframe>
                    </div>

                </label>
            </li>
            <li>
                <input id="po_cc_merchantpage" type="radio" name="payment_option" value="cc_merchantpage" style="display: none">
                <label class="payment-option" for="po_cc_merchantpage">
                    <img src="assets/img/cc.png" alt="">
                    <span class="name">Pay with credit cards (Merchant Page)</span>
                    <em class="seperator hidden"></em>
                    <div class="demo-container hidden"> <!--  Area for the iframe section -->
                        <iframe src="" frameborder="0"></iframe>
                    </div>

                </label>
            </li>
            <li>
                <input id="po_installments" type="radio" name="payment_option" value="installments" style="display: none">
                <label class="payment-option" for="po_installments">
                    <img src="assets/img/installment.png" alt="">
                    <span class="name"> Pay with installments</span>
                    <em class="seperator hidden"></em>
                </label>
            </li>
            <li>
                <input id="po_naps" type="radio" name="payment_option" value="naps" style="display: none">
                <label class="payment-option" for="po_naps">
                    <img src="assets/img/naps.png" alt="">
                    <span class="name">Pay with NAPS</span>
                    <em class="seperator hidden"></em>
                </label>
            </li>
            <li>
                <input id="po_sadad" type="radio" name="payment_option" value="sadad" style="display: none">
                <label class="payment-option" for="po_sadad">
                    <img src="assets/img/sadaad.png" alt="">
                    <span class="name">Pay with SADAD</span>
                    <em class="seperator hidden"></em>
                </label>
            </li>
        </ul>
    </section>

    <div class="h-seperator"></div>

    <section class="actions">
        <a class="back" href="#">Back</a>
        <a class="continue" id="btn_continue" href="javascript:void(0)">Continue</a>
    </section>
    <script type="text/javascript" src="vendors/jquery.min.js"></script>
        <script type="text/javascript" src="assets/js/checkout.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $('#btn_continue').click(function () {
                    var paymentMethod = $('input:radio[name=payment_option]:checked').val();
                    if(paymentMethod == '' || paymentMethod === undefined || paymentMethod === null) {
                        alert('Pelase Select Payment Method!');
                        return;
                    }
                    if(paymentMethod == 'cc_merchantpage') {
                        window.location.href = 'confirm-order.php?payment_method='+paymentMethod;
                    }
                    else{
                        getPaymentPage(paymentMethod);
                    }
                });
            });
        </script>
<?php include('footer.php') ?>