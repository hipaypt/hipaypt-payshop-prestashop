<?php
define('HEX_CHARS',    '0123456789abcdef');
define('BASE62_CHARS', '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
//include(dirname(__FILE__).'/nusoap/lib/nusoap.php');

class CompraFacil {
    private $webserviceUrl   = "";
    private $username        = "";
    private $password        = "";
	private $paymentCallback = "modules/hipaypayshop/payment_hipaypayshop.php";
	private $refObject		= null;
	var $error				= "";
	var $type				= 1;
	var $recordKey			= "";
	var $cypheredRecordKey	= "";
	var $reference			= "";
	var $userid 			= "";
	var $adpackid 			= "";
	var $md5 				= "";
	var $information		= "";
	var $name				= "";
	var $address			= "";
	var $postalCode			= "";
	var $place				= "";
	var $nif				= "";
	var $phone				= "";
	var $email				= "";

    function pickSoap($serverpath,$parameters,$method){
        switch($method){
            case "GenerateReference":
                if (!class_exists('soapclient')){ 
                    require_once dirname(__FILE__).'/nusoap/lib/nusoap.php';
                    $action='http://hm.comprafacil.pt/SIBSClick/webservice/SaveCompraToBDValor2';
                
                    $client = new soapclient($serverpath);

                    $msg=$client->serializeEnvelope('<SaveCompraToBDValor2 xmlns="http://hm.comprafacil.pt/SIBSClick/webservice/"><origem>'.$parameters['origem'].'</origem><IDCliente>'.$parameters['IDCliente'].'</IDCliente><password>'.$parameters['password'].'</password><valor>'.$parameters['valor'].'</valor><informacao>'.$parameters['informacao'].'</informacao><nome></nome><morada></morada><codPostal></codPostal><localidade></localidade><NIF></NIF><RefExterna></RefExterna><telefoneContacto></telefoneContacto><email>'.$parameters['email'].'</email><IDUserBackoffice>'.$parameters['IDUserBackoffice'].'</IDUserBackoffice></SaveCompraToBDValor2>','',array(),'document', 'literal');

                    $response = $client->send($msg,$action);
                    $result=$response['SaveCompraToBDValor2Result'];
                    
                    if($result == "true"){
                        $res = new stdClass();
                        $res->SaveCompraToBDValor2Result = (bool)$response['SaveCompraToBDValor2Result'];
                        $res->referencia = $response['referencia'];
                        $res->entidade = $response['entidade'];
                        $res->valorOut = $response['valorOut'];
                        $res->error = $response['error'];
                        return $res;
                    }else{
                        return false;
                    }
                }else{
                    $client = new SoapClient($serverpath);
                    return $client->SaveCompraToBDValor2($parameters);
                }
            break;
            case "VerifyPayment":
                if (!class_exists('soapclient')){ 
                    require_once dirname(__FILE__).'/nusoap/lib/nusoap.php';
                    $action='http://hm.comprafacil.pt/SIBSClick/webservice/getInfoCompra';
                
                    $client = new soapclient($serverpath);

                    $msg=$client->serializeEnvelope('<getInfoCompra xmlns="http://hm.comprafacil.pt/SIBSClick/webservice/"><IDCliente>'.$parameters['IDCliente'].'</IDCliente><password>'.$parameters['password'].'</password><referencia>'.$parameters['referencia'].'</referencia></getInfoCompra>','',array(),'document', 'literal');

                    $response = $client->send($msg,$action);
                    
                    if($response['getInfoCompraResult'] == "true"){
                        $res = new stdClass();
                        if($response['pago']=="false"){
                            $res->pago = (bool)FALSE;    
                        }else{
                            $res->pago = (bool)TRUE;    
                        }
                        
                        $res->getInfoCompraResult = (bool)$response['getInfoCompraResult'];
                        $res->estado = $response['estado'];
                        $res->error = $response['error'];
                        $res->dataUltimoPagamento = $response['dataUltimoPagamento'];
                        $res->TotalPagamentos = $response['TotalPagamentos'];
                        return $res;
                    }else{
                        return false;
                    }
                }else{
                    $client = new SoapClient($serverpath);
                    return $client->getInfoCompra ($parameters);
                }
            break;
        }
    }

    function __construct() {
       $this->webserviceUrl   = Configuration::get('CFPS_WEBSERVICEURL');
       $this->username        = Configuration::get('CFPS_USERNAME');
       $this->password        = Configuration::get('CFPS_PASSWORD');
   }
    
	function fillUserInfo($args){
		$this->name = $args[username];
		$this->email = $args[email];
		
        $this->information = '[PRESTASHOP] USER_ID: '.$args[user_id].' USER_NAME: '.$args[username].' USER_EMAIL:'.$args[email].' PAYMENT ID:'.$args[paymentid].' PRODUCTS:'.$args[products];
	}

	function GenerateReference($value, $kind=1) {
		try
		{
			$this->paymentCallback = Tools::getHttpHost(true).__PS_BASE_URI__.$this->paymentCallback.'?payment='.$this->md5;


			$parameters = array(
			"origem" => $this->paymentCallback,
			"IDCliente" => $this->username,
			"password" => $this->password,
			"valor" => $value,
			"informacao" => $this->information,
			"nome" => $this->name,
			"morada" => $this->address,
			"codPostal" => $this->postalCode,
			"localidade" => $this->place,
			"NIF" => $this->nif,
			"RefExterna" => "5",
			"telefoneContacto" => $this->phone,
			"email" => $this->email,
			"IDUserBackoffice" => -1			
			);


			$client = new SoapClient($this->webserviceUrl);
			$res = $client->SaveCompraToBDValor2 ($parameters); 
	
			if ($res->SaveCompraToBDValor2Result)
			{
				$newReference = new CFReference($res->dataLimitePagamento, $res->referencia, $res->valorOut);
				return $newReference;
			}
			else{
				$error = $res->error;
                $this->error = $res->error;
				return null;
			}
		}
		catch (Exception $e){
			echo $e->getMessage();
			die;
			return null;
		}
	}
    
    function verify_payment($reference) {
        try
        {
            $parameters = array(
                "referencia" => $reference,
                "IDCliente" => $this->username,
                "password" => $this->password,
            );

            $res =  CompraFacil::pickSoap($this->webserviceUrl,$parameters,"VerifyPayment");

            if ($res->getInfoCompraResult) {
                return $res->pago;
            }
            else
            {
                return false;
            }

        }
        catch (Exception $e){
            echo $e->getMessage();
            return false;
        }
    }

}

class CFReference {
	var $limitdate		= "";
	var $reference		= "";
	var $value			= "";

	function CFReference($limitdate, $ref, $val) {
		$this->limitdate = $limitdate;
		$this->reference = $ref;
		$this->value = $val;
	}
}

/*********************************************/
function ConvertFromArbitraryBase($Str, $Chars)
/*********************************************/
{
	/*
	 Converts from an arbitrary-base string to a decimal string
	 */

	if (ereg('^[' . $Chars . ']+$', $Str))
	{
		$Result = '0';

		for ($i=0; $i<strlen($Str); $i++)
		{
			if ($i != 0) $Result = bcmul($Result, strlen($Chars));
			$Result = bcadd($Result, strpos($Chars, $Str[$i]));
		}

		return $Result;
	}

	return false;
}

/*******************************************/
function ConvertToArbitraryBase($Str, $Chars)
/*******************************************/
{
	/*
	 Converts from a decimal string to an arbitrary-base string
	 */

	if (ereg('^[0-9]+$', $Str))
	{
		$Result = '';

		do
		{
			$Result .= $Chars[bcmod($Str, strlen($Chars))];
			$Str = bcdiv($Str, strlen($Chars));
		}
		while (bccomp($Str, '0') != 0);

		return strrev($Result);
	}

	return false;
}

/**********************/
function CustomMD5($Str)
/**********************/
{
	return ConvertToArbitraryBase(ConvertFromArbitraryBase(md5($Str), HEX_CHARS), BASE62_CHARS);
}
?>