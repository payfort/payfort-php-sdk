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
                <input id="po_creditcard" type="radio" name="payment_option" value="creditcard"  checked="checked" style="display: none">
                <label class="payment-option active" for="po_creditcard">
                    <img src="../assets/img/cc.png" alt="">
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
                    <img src="../assets/img/cc.png" alt="">
                    <span class="name">Pay with credit cards (Merchant Page)</span>
                    <em class="seperator hidden"></em>
                    <div class="demo-container hidden"> <!--  Area for the iframe section -->
                        <iframe src="" frameborder="0"></iframe>
                    </div>

                </label>
            </li>
            <li>
                <input id="po_cc_merchantpage2" type="radio" name="payment_option" value="cc_merchantpage2"  style="display: none">
                <label class="payment-option" for="po_cc_merchantpage2">
                    <img src="../assets/img/cc.png" alt="">
                    <span class="name">Pay with credit cards (Merchant Page 2.0)</span>
                    <em class="seperator hidden"></em>
                </label>
                <div class="details well" style="display: none;">
                    <form id="frm_payfort_payment_merchant_page2" class="form-horizontal">
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="payfort_fort_mp2_card_holder_name">Name on Card</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="card_holder_name" id="payfort_fort_mp2_card_holder_name" placeholder="Card Holder's Name" maxlength="50">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="payfort_fort_mp2_card_number">Card Number</label>
                            <div class="col-sm-9">
                                <input type="text" class="form-control" name="card)number" id="payfort_fort_mp2_card_number" placeholder="Debit/Credit Card Number" maxlength="16">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="payfort_fort_mp2_expiry_month">Expiration Date</label>
                            <div class="col-sm-9">
                                <div class="row">
                                    <div class="col-xs-3">
                                        <select class="form-control col-sm-2" name="expiry_month" id="payfort_fort_mp2_expiry_month">
                                            <option value="01">Jan (01)</option>
                                            <option value="02">Feb (02)</option>
                                            <option value="03">Mar (03)</option>
                                            <option value="04">Apr (04)</option>
                                            <option value="05">May (05)</option>
                                            <option value="06">June (06)</option>
                                            <option value="07">July (07)</option>
                                            <option value="08">Aug (08)</option>
                                            <option value="09">Sep (09)</option>
                                            <option value="10">Oct (10)</option>
                                            <option value="11">Nov (11)</option>
                                            <option value="12">Dec (12)</option>
                                        </select>
                                    </div>
                                    <div class="col-xs-3">
                                        <select class="form-control" name="expiry_year" id="payfort_fort_mp2_expiry_year">
                                            <?php
                                            $today = getdate();
                                            $year_expire = array();
                                            for ($i = $today['year']; $i < $today['year'] + 11; $i++) {
                                                    $year_expire[] = array(
                                                            'text'  => strftime('%Y', mktime(0, 0, 0, 1, 1, $i)),
                                                            'value' => strftime('%y', mktime(0, 0, 0, 1, 1, $i)) 
                                                    );
                                            }
                                            ?>
                                            <?php
                                            foreach($year_expire  as $year) {
                                                echo "<option value={$year['value']}>{$year['text']}</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 control-label" for="payfort_fort_mp2_cvv">Card CVV</label>
                            <div class="col-sm-3">
                                <input type="text" class="form-control" name="cvv" id="payfort_fort_mp2_cvv" placeholder="Security Code" maxlength="4">
                            </div>
                        </div>
                    </form>
                </div>
            </li>
            
            
            
            
            
            
            
            <li>
                <input id="po_installments" type="radio" name="payment_option" value="installments" style="display: none">
                <label class="payment-option" for="po_installments">
                    <img src="../assets/img/installment.png" alt="">
                    <span class="name"> Pay with installments (Redirection)</span>
                    <em class="seperator hidden"></em>
                </label>
            </li>
            <li>
                <input id="po_installments_merchantpage" type="radio" name="payment_option" value="installments_merchantpage" style="display: none">
                <label class="payment-option" for="po_installments_merchantpage">
                    <img src="../assets/img/installment.png" alt="">
                    <span class="name"> Pay with installments (Merchant Page)</span>
                    <em class="seperator hidden"></em>
                </label>
            </li>
            
            
            
            
            
            
            <li>
                <input id="po_naps" type="radio" name="payment_option" value="naps" style="display: none">
                <label class="payment-option" for="po_naps">
                    <img src="../assets/img/naps.png" alt="">
                    <span class="name">Pay with NAPS</span>
                    <em class="seperator hidden"></em>
                </label>
            </li>
            <li>
                <input id="po_sadad" type="radio" name="payment_option" value="sadad" style="display: none">
                <label class="payment-option" for="po_sadad">
                    <img src="../assets/img/sadaad.png" alt="">
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
    <script type="text/javascript" src="../vendors/jquery.min.js"></script>
        <script type="text/javascript" src="../assets/js/jquery.creditCardValidator.js"></script>
        <script type="text/javascript" src="../assets/js/checkout.js"></script>
        <script type="text/javascript">
            $(document).ready(function () {
                $('input:radio[name=payment_option]').click(function () {
                    $('input:radio[name=payment_option]').each(function () {
                        if ($(this).is(':checked')) {
                            $(this).addClass('active');
                            $(this).parent('li').children('label').css('font-weight', 'bold');
                            $(this).parent('li').children('div.details').show();
                        }
                        else {
                            $(this).removeClass('active');
                            $(this).parent('li').children('label').css('font-weight', 'normal');
                            $(this).parent('li').children('div.details').hide();
                        }
                    });
                });
                $('#btn_continue').click(function () {
                    var paymentMethod = $('input:radio[name=payment_option]:checked').val();
                    if(paymentMethod == '' || paymentMethod === undefined || paymentMethod === null) {
                        alert('Pelase Select Payment Method!');
                        return;
                    }
                    if(paymentMethod == 'cc_merchantpage' || paymentMethod == 'installments_merchantpage') {
                        window.location.href = 'confirm-order.php?payment_method='+paymentMethod;
                    }
                    if(paymentMethod == 'cc_merchantpage2') {
                        var isValid = payfortFortMerchantPage2.validateCcForm();
                        if(isValid) {
                            getPaymentPage(paymentMethod);
                        }
                    }
                    else{
                        getPaymentPage(paymentMethod);
                    }
                });
            });
        </script>
<?php include('footer.php') ?>