<?php
/*
* 2007-2012 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2012 PrestaShop SA
*  @version  Release: $Revision: 14239 $
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

include(dirname(__FILE__).'/../../config/config.inc.php');
include(dirname(__FILE__).'/../../header.php');
include(dirname(__FILE__).'/hipaypayshop.php');

$cf_url = Configuration::get('CFPS_WEBSERVICEURL');
$cf_user = Configuration::get('CFPS_USERNAME');
$cf_pass = Configuration::get('CFPS_PASSWORD');

if(!$cf_url OR !$cf_user OR !$cf_pass){
    die(Tools::displayError('This payment is not configured correctly, Please contact us at your earliest convenience.'));
}

$hipaypayshop = new hipaypayshop();

if ($cart->id_customer == 0 OR $cart->id_address_delivery == 0 OR $cart->id_address_invoice == 0 OR !$hipaypayshop->active)
	Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');

// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
$authorized = false;
foreach (Module::getPaymentModules() as $module)
	if ($module['name'] == 'hipaypayshop')
	{
		$authorized = true;
		break;
	}
if (!$authorized)
	die(Tools::displayError('This payment method is not available.'));

$customer = new Customer((int)$cart->id_customer);

if (!Validate::isLoadedObject($customer))
	Tools::redirectLink(__PS_BASE_URI__.'order.php?step=1');

    $paymentArray = array();
    $valor=0;

    $products=$cart->getProducts();
    foreach($products as $product){
        $paymentArray['products'][] = $product[name];
        if (!$paymentArray['prod_names']){
            $paymentArray['prod_names'] = $product['cart_quantity'].'x '.$product[name];
        }else{
            $paymentArray['prod_names'] .= ', '.$product['cart_quantity'].'x '.$product[name];
        }
    }
    $valor = (float)$cart->getOrderTotal(true, Cart::BOTH);

    if ($valor > 0) {
        $paymentArray['user_id'] = $cart->id_customer;
        $paymentArray['order_id'] = $cart->id;
        $paymentArray['reference_date'] = date("Y-m-d H:i:s");
        
        $db = Db::getInstance();
        $db->ExecuteS('INSERT INTO `'._DB_PREFIX_.'hipaypayshop` (user_id,reference_date,products) VALUES ('.$paymentArray['user_id'].',"'.$paymentArray['reference_date'].'","'.$paymentArray['prod_names'].'")');
        $lastid = $db->Insert_ID(); //$lastid = mysql_insert_id();
        $paymentArray[md5] = md5(time().$lastid);

        // Call CF
        include(dirname(__FILE__).'/class.hipaypayshop.php');
        $compraFacil = new CompraFacil();
        $compraFacil->fillUserInfo(Array('user_id'=>$customer->id,'username'=>$customer->firstname.' '.$customer->lastname,'email'=>$customer->email,'paymentid'=>$lastid,'products'=>$paymentArray['prod_names']));
        $compraFacil->recordKey = $lastid;
        $compraFacil->md5 = $paymentArray[md5];
        $referenciaMB = $compraFacil->GenerateReference(sprintf("%01.2f",$valor));
        
        if($compraFacil->error){
            $error = $compraFacil->error;
            global $smarty;
            $db = Db::getInstance();
            $db->ExecuteS("UPDATE `"._DB_PREFIX_."hipaypayshop` SET `md5`='".$error."' WHERE `id`='".$lastid."' AND `user_id`='".$paymentArray[user_id]."';");
            $smarty->assign(array(
                'error'              => $error,
            ));
            $smarty->display(dirname(__FILE__).'/payment_return.tpl');
            return false;
        }

        $paymentArray[reference] = $referenciaMB->reference;
        $paymentArray[limitdate] = $referenciaMB->limitdate;
        $paymentArray[value] = $referenciaMB->value;
        $db = Db::getInstance();
        $db->ExecuteS("UPDATE `"._DB_PREFIX_."hipaypayshop` SET `reference`='".$paymentArray[reference]."', `limitdate`='".$paymentArray[limitdate]."', `value`='".$paymentArray[value]."', `md5`='".$paymentArray[md5]."' WHERE `id`='".$lastid."' AND `user_id`='".$paymentArray[user_id]."';");
    }

$mailVars =	array(
	'{limitdate}' => $paymentArray[limitdate],
	'{reference}' => $paymentArray[reference],
	'{value}' => $paymentArray[value]);

$hipaypayshop->validateOrder((int)$cart->id, Configuration::get('HIPAYPS_AUTHORIZATION_OS'), $paymentArray[value], $hipaypayshop->displayName, NULL, $mailVars, (int)$currency->id, false, $customer->secure_key);

$db = Db::getInstance();
$db->ExecuteS("UPDATE `"._DB_PREFIX_."hipaypayshop` SET `order_id`='".$hipaypayshop->currentOrder."' WHERE `id`='".$lastid."' AND `user_id`='".$paymentArray[user_id]."';");

Tools::redirectLink(__PS_BASE_URI__.'order-confirmation.php?id_cart='.(int)$cart->id.'&id_module='.(int)$hipaypayshop->id.'&id_order='.$hipaypayshop->currentOrder.'&key='.$customer->secure_key.'&payshop='.$lastid);