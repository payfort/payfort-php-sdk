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