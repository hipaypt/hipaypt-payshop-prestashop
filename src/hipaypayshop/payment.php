<?php
 
include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/hipaypayshop.php');
 
if (!$cookie->isLogged())
    Tools::redirect('authentication.php?back=order.php');
 
$hipaypayshop = new hipaypayshop();
echo $hipaypayshop->execPayment($cart);
 
include_once(dirname(__FILE__).'/../../footer.php');
 
?>