<?php
/**
 * validation.php
 *
 * Copyright (c) 2011 SSLWireless
 * 
 * LICENSE:
 * 
 * This payment module is free software; you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published
 * by the Free Software Foundation; either version 3 of the License, or (at
 * your option) any later version.
 * 
 * This payment module is distributed in the hope that it will be useful, but
 * WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
 * or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser General Public
 * License for more details.

 *
 * @author     JM redwan <me@jmredwan.com>
 * @version    2.1.0
 * @date       30/05/2016
 * 
 * @copyright  2016 SSL Wireless
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://www.sslcommerz.com.bd
 */

include( dirname(__FILE__).'/../../config/config.inc.php' );
include( dirname(__FILE__).'/sslcommerz.php' );
include( dirname(__FILE__).'/sslcommerz_common.inc' );



		if (isset($_POST['tran_id'])) {
			$order_id = $_POST['tran_id'];
		} else {
			$order_id = NULL;
		}	


if(!empty($order_id)){

                if (isset($_POST['amount'])) {
                    $total=$_POST['amount'];
					
				}else
                	{
                    $total='';	
                   
                }
				if(isset($_POST['val_id'])){
					$val_id = urldecode($_POST['val_id']); 
					}
				else {
					 $val_id = ''; 
					}



if(empty($val_id)){
						if(Configuration::get('sslcommerz_MODE')=='live'){
						  $valid_url_own = ("https://securepay.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php?tran_id=".$order_id."&Store_Id=".Configuration::get('sslcommerz_MERCHANT_ID')."&Store_Passwd=".Configuration::get('sslcommerz_MERCHANT_KEY')."&v=1&format=json"); 
						 
						  } else{
							 $valid_url_own = ("https://sandbox.sslcommerz.com/validator/api/merchantTransIDvalidationAPI.php?tran_id=".$order_id."&Store_Id=".Configuration::get('sslcommerz_MERCHANT_ID')."&Store_Passwd=".Configuration::get('sslcommerz_MERCHANT_KEY')."&v=1&format=json");  
						  }

						 
			$ownvalid = curl_init();
			curl_setopt($ownvalid, CURLOPT_URL, $valid_url_own);
			curl_setopt($ownvalid, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ownvalid, CURLOPT_SSL_VERIFYHOST, false);
			curl_setopt($ownvalid, CURLOPT_SSL_VERIFYPEER, false);
			
			$ownvalid_result = curl_exec($ownvalid);
			
			$ownvalid_code = curl_getinfo($ownvalid, CURLINFO_HTTP_CODE);
			
			if($ownvalid_code == 200 && !( curl_errno($ownvalid)))
			{
				$result_own = json_decode($ownvalid_result, true);
				$lastupdate_no = $result_own['no_of_trans_found']-1;	
				$own_data = $result_own['element']; 
//echo $lastupdate_no;
//echo '<pre>';
//print_r($own_data);
				$val_id = $own_data[$lastupdate_no]['val_id'];
			
			}
						 
					 
						 
}



if(Configuration::get('sslcommerz_MODE')=='live'){
                $requested_url = ("https://securepay.sslcommerz.com/validator/api/validationserverAPI.php?val_id=".$val_id."&Store_Id=".Configuration::get('sslcommerz_MERCHANT_ID')."&Store_Passwd=".Configuration::get('sslcommerz_MERCHANT_KEY')."&v=1&format=json");
                } else{
               $requested_url = ("https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php?val_id=".$val_id."&Store_Id=".Configuration::get('sslcommerz_MERCHANT_ID')."&Store_Passwd=".Configuration::get('sslcommerz_MERCHANT_KEY')."&v=1&format=json");  
                }  


//echo $requested_url;

 $handle = curl_init();
curl_setopt($handle, CURLOPT_URL, $requested_url);
curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);

$result = curl_exec($handle);


$code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

if($code == 200 && !( curl_errno($handle)))
{	

	# TO CONVERT AS ARRAY
	# $result = json_decode($result, true);
	# $status = $result['status'];	
	
	# TO CONVERT AS OBJECT
	$result = json_decode($result);
		//print_r($result);
	# TRANSACTION INFO
	$status = $result->status;	
	$tran_date = $result->tran_date;
	$tran_id = $result->tran_id;
	$val_id = $result->val_id;
	$amount = $result->amount;
	$store_amount = $result->store_amount;
	$currency_amount = $result->currency_amount;
	$bank_tran_id = $result->bank_tran_id;
	$card_type = $result->card_type;
	$currency_amount= $result->currency_amount;
	# ISSUER INFO
	$card_no = $result->card_no;
	$card_issuer = $result->card_issuer;
	$card_brand = $result->card_brand;
	$card_issuer_country = $result->card_issuer_country;
	$card_issuer_country_code = $result->card_issuer_country_code;   
	

$success_URL = $result->value_a;
$failed_URL = $result->value_b;

	//Payment Risk Status
	$risk_level = $result->risk_level;
	$risk_title = $result->risk_title;
	
	echo '<pre>'; print_r($result); echo '</pre>'; eixt;
                    if($status=='VALID')
                    {
                        if($risk_level==0){ $status = 'success';
$return_url = $success_URL;
}
                        if($risk_level==1){ $status = 'risk';
$return_url = $failed_URL;
} 
                       
                    }
                    elseif($status=='VALIDATED'){
                        if($risk_level==0){ $status = 'success';
$return_url = $success_URL;
}
                        if($risk_level==1){ $status = 'risk';
$return_url = $failed_URL;
} 
                     }
                    else
                    {
                         $status = 'failed';
$return_url = $failed_URL;
                    }
                }




$cart = new Cart((int) $order_id);

$total = $currency_amount;
$transaction_id = $bank_tran_id;
    $sslcommerz = new sslcommerz();





        switch( $status )
        {
            case 'success':


                // Update the purchase status
$sslcommerz->validateOrder((int)$order_id, _PS_OS_PAYMENT_, (float)$currency_amount , 
                    $sslcommerz->displayName, NULL, array('transaction_id'=>$transaction_id), NULL, false, $cart->secure_key);

Tools::redirect($return_url);
                
                break;

            case 'risk':
echo $return_url;
                // If payment fails, delete the purchase log
          $sslcommerz->validateOrder((int)$order_id, _PS_OS_ERROR_, (float)$currency_amount , 
                    $sslcommerz->displayName, NULL, array('transaction_id'=>$transaction_id), NULL, false, $cart->secure_key);
Tools::redirect($return_url);
                break;

            case 'failed':
 // If payment fails, delete the purchase log
          $sslcommerz->validateOrder((int)$order_id, _PS_OS_ERROR_, (float)$currency_amount , 
                    $sslcommerz->displayName, NULL, array('transaction_id'=>$transaction_id), NULL, false, $cart->secure_key);
Tools::redirect($return_url);
                break;

            default:
 // If payment fails, delete the purchase log
          $sslcommerz->validateOrder((int)$order_id, _PS_OS_ERROR_, (float)$currency_amount , 
                    $sslcommerz->displayName, NULL, array('transaction_id'=>$transaction_id), NULL, false, $cart->secure_key);
Tools::redirect($return_url);
            break;
        }




}
?>
<center>Processing to Order History Page</center>
<div class="spinner"></div>

<style type="text/css">
.spinner {
  width: 40px;
  height: 40px;
  background-color: #333;

  margin: 100px auto;
  -webkit-animation: sk-rotateplane 1.2s infinite ease-in-out;
  animation: sk-rotateplane 1.2s infinite ease-in-out;
}

@-webkit-keyframes sk-rotateplane {
  0% { -webkit-transform: perspective(120px) }
  50% { -webkit-transform: perspective(120px) rotateY(180deg) }
  100% { -webkit-transform: perspective(120px) rotateY(180deg)  rotateX(180deg) }
}

@keyframes sk-rotateplane {
  0% { 
    transform: perspective(120px) rotateX(0deg) rotateY(0deg);
    -webkit-transform: perspective(120px) rotateX(0deg) rotateY(0deg) 
  } 50% { 
    transform: perspective(120px) rotateX(-180.1deg) rotateY(0deg);
    -webkit-transform: perspective(120px) rotateX(-180.1deg) rotateY(0deg) 
  } 100% { 
    transform: perspective(120px) rotateX(-180deg) rotateY(-179.9deg);
    -webkit-transform: perspective(120px) rotateX(-180deg) rotateY(-179.9deg);
  }
}
</style>

