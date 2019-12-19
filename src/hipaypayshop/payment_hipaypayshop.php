<?php
    include(dirname(__FILE__).'/../../config/config.inc.php');
    include(dirname(__FILE__).'/../../header.php');
    include(dirname(__FILE__).'/hipaypayshop.php');
    
    $md5 = $_GET["payment"];
    $db = Db::getInstance();
    $payment = $db->ExecuteS('SELECT * FROM `'._DB_PREFIX_.'hipaypayshop` WHERE `md5` ="'.$md5.'";');
    $payment = $payment[0];
    
    if (!$payment) {
        die(Tools::displayError('Código de pagamento inválido.'));
    }

    if ($payment[status] == 1) {
        die(Tools::displayError('Pagamento ja efectuado.'));
    }

    //Confirmar pagamento CF
    //include(dirname(__FILE__).'/class.hipaypayshop.php');
    //$compraFacil = new CompraFacil();
    //if(!$compraFacil->verify_payment($payment[reference])){ //TODOWFX: trocar lógica depois dos testes
    //    die(Tools::displayError("A referência ".$payment[reference]." ainda não foi paga"));
    //}

    $db = Db::getInstance();
    $db->ExecuteS('INSERT INTO `'._DB_PREFIX_.'order_history` (id_order,id_order_state,date_add) VALUES ('.$payment['order_id'].',12,"'.date("Y-m-d H:i:s").'")');
    $db = Db::getInstance();
    $db->ExecuteS("UPDATE `"._DB_PREFIX_."hipaypayshop` SET `status`='1', `payment_date`='".date("Y-m-d H:i:s")."';");

	$db = Db::getInstance();
    $db->ExecuteS("UPDATE `"._DB_PREFIX_."orders` SET `current_state`='".(int)Configuration::get('HIPAYPS_CAPTURE_OS')."' WHERE id_order=" . $payment['order_id']);

    $id_land = Language::getIdByIso($defaultCountry->iso_code);     //Set the English mail template
    $template_name = 'hipaypayshop_confirm'; //Specify the template file name
    $title = Mail::l('Payment Confirmed'); //Mail subject with translation
    $from = Configuration::get('PS_SHOP_EMAIL');   //Sender's email
    $fromName = Configuration::get('PS_SHOP_NAME'); //Sender's name
    $mailDir = dirname(__FILE__).'/mails/'; //Directory with message templates
    $templateVars =    array(
        '{order_name}' => $payment[order_id]);
    $send = Mail::Send($id_land, $template_name, $title, $templateVars, $from, $fromName, $from, $fromName, $fileAttachment, NULL, $mailDir);
    exit();