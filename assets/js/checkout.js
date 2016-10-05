function getPaymentPage(paymentMethod) {
    var check3ds = getUrlParameter('3ds');
    var url = 'route.php?r=getPaymentPage';
    if(check3ds == 'no') {
       url = url+'&3ds=no'; 
    }
    $.ajax({
       url: url, 
       type: 'post',
       dataType: 'json',
       data: {paymentMethod: paymentMethod},
       success: function (response) {
            if (response.form) {
                $('body').append(response.form);
                if(response.paymentMethod == 'cc_merchantpage') {
                    showMerchantPage(response.url);
                }
                else if(response.paymentMethod == 'cc_merchantpage2') {
                    var expDate = $('#payfort_fort_mp2_expiry_year').val()+''+$('#payfort_fort_mp2_expiry_month').val();
                    var mp2_params = {};
                    mp2_params.card_holder_name = $('#payfort_fort_mp2_card_holder_name').val();
                    mp2_params.card_number = $('#payfort_fort_mp2_card_number').val();
                    mp2_params.expiry_date = expDate;
                    mp2_params.card_security_code = $('#payfort_fort_mp2_cvv').val();
                    $.each(mp2_params, function(k, v){
                        $('<input>').attr({
                            type: 'hidden',
                            id: k,
                            name: k,
                            value: v
                        }).appendTo('#payfort_payment_form'); 
                    });
                    $('#payfort_payment_form input[type=submit]').click();
                }
                else{
                    $('#payfort_payment_form input[type=submit]').click();
                }
            }
       }
    });
}
function showMerchantPage(merchantPageUrl) {
    if($("#payfort_merchant_page").size()) {
        $( "#payfort_merchant_page" ).remove();
    }
    $('<iframe name="payfort_merchant_page" id="payfort_merchant_page" height="430px" width="100%" frameborder="0" scrolling="no"></iframe>').appendTo('#pf_iframe_content');
    
    $( "#payfort_merchant_page" ).attr("src", merchantPageUrl);
    $( "#payfort_payment_form" ).attr("action", merchantPageUrl);
    $( "#payfort_payment_form" ).attr("target","payfort_merchant_page");
    $( "#payfort_payment_form" ).attr("method","POST");
    $('#payfort_payment_form input[type=submit]').click();
    //$( "#payfort_payment_form" ).submit();
    $( "#div-pf-iframe" ).show();
}

var getUrlParameter = function getUrlParameter(sParam) {
    var sPageURL = decodeURIComponent(window.location.search.substring(1)),
        sURLVariables = sPageURL.split('&'),
        sParameterName,
        i;

    for (i = 0; i < sURLVariables.length; i++) {
        sParameterName = sURLVariables[i].split('=');

        if (sParameterName[0] === sParam) {
            return sParameterName[1] === undefined ? true : sParameterName[1];
        }
    }
};


var payfortFort = (function () {
   return {
        validateCreditCard: function(element) {
            var isValid = false;
            var eleVal = $(element).val();
            eleVal = this.trimString(element.val());
            eleVal = eleVal.replace(/\s+/g, '');
            $(element).val(eleVal);
            $(element).validateCreditCard(function(result) {
                /*$('.log').html('Card type: ' + (result.card_type == null ? '-' : result.card_type.name)
                         + '<br>Valid: ' + result.valid
                         + '<br>Length valid: ' + result.length_valid
                         + '<br>Luhn valid: ' + result.luhn_valid);*/
                isValid = result.valid;
            });
            return isValid;
        },
        validateCardHolderName: function(element) {
            $(element).val(this.trimString(element.val()));
            var cardHolderName = $(element).val();
            if(cardHolderName.length > 50) {
                return false;
            }
            return true;
        },
        validateCvc: function(element) {
            $(element).val(this.trimString(element.val()));
            var cvc = $(element).val();
            if(cvc.length > 4 || cvc.length == 0) {
                return false;
            }
            if(!this.isPosInteger(cvc)) {
                return false;
            }
            return true;
        },
        isDefined: function(variable) {
            if (typeof (variable) === 'undefined' || typeof (variable) === null) {
                return false;
            }
            return true;
        },
        trimString: function(str){
            return str.trim();
        },
        isPosInteger: function(data) {
            var objRegExp  = /(^\d*$)/;
            return objRegExp.test( data );
        }
   };
})();

var payfortFortMerchantPage2 = (function () {
    return {
        validateCcForm: function () {
            this.hideError();
            var isValid = payfortFort.validateCardHolderName($('#payfort_fort_mp2_card_holder_name'));
            if(!isValid) {
                this.showError('Invalid Card Holder Name');
                return false;
            }
            isValid = payfortFort.validateCreditCard($('#payfort_fort_mp2_card_number'));
            if(!isValid) {
                this.showError('Invalid Credit Card Number');
                return false;
            }
            isValid = payfortFort.validateCvc($('#payfort_fort_mp2_cvv'));
            if(!isValid) {
                this.showError('Invalid Card CVV');
                return false;
            }
            return true;
        },
        showError: function(msg) {
            alert(msg);
        },
        hideError: function() {
            return;
        }
    };
})();
