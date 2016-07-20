<?php

/**
 * @copyright Copyright PayFort 2012-2016 
 */

if(!isset($_REQUEST['r'])) {
    echo 'Page Not Found!';
    exit;
}
require_once 'PayfortIntegration.php';
$objFort = new PayfortIntegration();
if($_REQUEST['r'] == 'getPaymentPage') {
    $objFort->processRequest($_REQUEST['paymentMethod']);
}
elseif($_REQUEST['r'] == 'merchantPageReturn') {
    $objFort->processMerchantPageResponse();
}
elseif($_REQUEST['r'] == 'processResponse') {
    $objFort->processResponse();
}
else{
    echo 'Page Not Found!';
    exit;
}
?>

