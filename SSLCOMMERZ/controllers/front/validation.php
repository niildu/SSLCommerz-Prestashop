<?php
/*
* 2007-2015 PrestaShop
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
*  @copyright  2007-2015 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

/**
 * @since 1.5.0
 */

class SslcommerzValidationModuleFrontController extends ModuleFrontController
{
	/**
	 * @see FrontController::postProcess()
	**/
	public function postProcess()
	{
	
	    $tran_id = $_POST['tran_id'];
	    $cart = $this->context->cart;
	    $sslcommerz = Module::getInstanceByName('SSLCOMMERZ');
		if ($cart->id_customer == 0 || $cart->id_address_delivery == 0 || $cart->id_address_invoice == 0 || !$this->module->active)
			Tools::redirect('index.php?controller=order&step=1');

		// Check that this payment option is still available in case the customer changed his address just before the end of the checkout process
		$authorized = false;
		foreach (Module::getPaymentModules() as $module)
		{
			if ($module['name'] == 'SSLCOMMERZ')
			{
				$authorized = true;
				break;
			}
		}
		if (!$authorized)
		{
			die($this->module->getTranslator()->trans('This payment method is not available.', array(), 'Modules.SSLCOMMERZ.Shop'));
		}
    
		$customer = new Customer($cart->id_customer);
		$currency = $this->context->currency;
		$total = (float)$cart->getOrderTotal(true, Cart::BOTH);
		$order_id = Order::getOrderByCartId((int)($tran_id));
		$val_id = $_POST['val_id'];
		
// 		echo $tran_id."----".$order_id."----".$val_id;exit;

		$sslc_mode = Configuration::get('MODE');
		if (!Validate::isLoadedObject($customer))
		{
			Tools::redirect('index.php?controller=order&step=1');
		}
		
	    if( $sslc_mode == 1 )
        {
            $valid_url_own = ("https://securepay.sslcommerz.com/validator/api/validationserverAPI.php?val_id=".$val_id."&Store_Id=".Configuration::get('SSLCOMMERZ_STORE_ID')."&Store_Passwd=".Configuration::get('SSLCOMMERZ_STORE_PASSWORD')."&v=1&format=json");
        }
        else
        {
            $valid_url_own = ("https://sandbox.sslcommerz.com/validator/api/validationserverAPI.php?val_id=".$val_id."&Store_Id=".Configuration::get('SSLCOMMERZ_STORE_ID')."&Store_Passwd=".Configuration::get('SSLCOMMERZ_STORE_PASSWORD')."&v=1&format=json");
        }
        
        // echo $valid_url_own."<br>";
        
        $objOrder = new Order($order_id);
        $history = new OrderHistory();
        $history->id_order = (int)$objOrder->id;
        $order_status = $objOrder->current_state;
        // echo $history->id_order;
        // echo $tran_id;
        // echo $objOrder->id;
        // print_r($_POST);
        // print_r($history);
        // exit;
        
        $success_URL = Tools::getHttpHost( true ).__PS_BASE_URI__.'index.php?controller=order-confirmation&id_cart='.$tran_id.'&id_module='.$this->module->id.'&id_order='.$history->id_order.'&key='.$customer->secure_key;
        $failed_URL = Tools::getHttpHost( true ).__PS_BASE_URI__.'index.php?controller=order-detail&id_cart='.$tran_id.'&id_module='.$this->module->id.'&id_order='.$history->id_order.'&key='.$customer->secure_key;

        if($_POST['status'] == 'VALID' && $order_status == 3)
        {
            if (isset($_POST['tran_id']) && isset($_POST['val_id']) && isset($_POST['amount'])) 
    		{
    			$handle = curl_init();
                curl_setopt($handle, CURLOPT_URL, $valid_url_own);
                curl_setopt($handle, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($handle, CURLOPT_SSL_VERIFYHOST, false);
                curl_setopt($handle, CURLOPT_SSL_VERIFYPEER, false);
                $result = curl_exec($handle);
                $code = curl_getinfo($handle, CURLINFO_HTTP_CODE);

                // print_r($result);exit;
                if($code == 200 && !( curl_errno($handle)))
                {	
                	# TO CONVERT AS ARRAY
                    # $result = json_decode($result, true);
                	# $status = $result['status'];
                	
                	
                	# TO CONVERT AS OBJECT
                	$result = json_decode($result);
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
    
                	//Payment Risk Status
                	$risk_level = $result->risk_level;
                	$risk_title = $result->risk_title;
                	
                	if($status=='VALID')
                    {
                        if($risk_level==0)
                        { 
                            $status = 'success';
                            $return_url = $success_URL;
                        }
                        if($risk_level==1)
                        { 
                            $status = 'risk';
                            $return_url = $failed_URL;
                        } 
                    }
                    elseif($status=='VALIDATED')
                    {
                        if($risk_level==0)
                        { 
                            $status = 'success';
                            $return_url = $success_URL;
                        }
                        if($risk_level==1)
                        { 
                            $status = 'risk';
                            $return_url = $failed_URL;
                        } 
                    }
                    else
                    {
                        $status = 'failed';
                        $return_url = $failed_URL;
                    }
                    
                    if($status == 'success')
                    {
                        if($order_status == 3)
                        {
                            $history->changeIdOrderState(2, (int)($objOrder->id));
                            Tools::redirect($return_url);
                        }
                        else
                        {
                            Tools::redirect($return_url);
                        }
                    }
                    elseif($status == 'risk')
                    {
                        if($order_status == 3)
                        {
                            $history->changeIdOrderState(8, (int)($objOrder->id));
                            Tools::redirect($return_url);
                        }
                        else
                        {
                            Tools::redirect($return_url);
                        }
                    }
                    elseif($status == 'failed')
                    {
                        if($order_status == 3)
                        {
                            $history->changeIdOrderState(8, (int)($objOrder->id));
                            Tools::redirect($return_url);
                        }
                        else
                        {
                            Tools::redirect($return_url);
                        }
                    }
                    else
                    {
                        // If payment fails, delete the purchase log
                        if($order_status == 3)
                        {
                            $history->changeIdOrderState(8, (int)($objOrder->id));
                            Tools::redirect($return_url);
                        }
                        else
                        {
                            Tools::redirect($return_url); 
                        }
                    }
                    
                    
                    // switch( $status )
                    // {
                    //     case 'success':
                    //         // Update the purchase status
                    //         $history->changeIdOrderState(2, (int)($objOrder->id));
                    //         //Tools::redirect($return_url);
                    //         echo '#1'.$status;
                    //         break;
                    //     case 'risk':
                    //         // If payment fails, delete the purchase log
                    //         $history->changeIdOrderState(8, (int)($objOrder->id));
                    //         //Tools::redirect($return_url);
                    //         echo '#2'.$status;
                    //         break;
                    //     case 'failed':
                    //         // If payment fails, delete the purchase log
                    //         $history->changeIdOrderState(8, (int)($objOrder->id));
                    //         //Tools::redirect($return_url);
                    //         echo '#3'.$status;
                    //         break;
                            
                    //         default:
                    //         // If payment fails, delete the purchase log
                    //         $history->changeIdOrderState(8, (int)($objOrder->id));
                    //         echo '#4'.$status;
                    //         //Tools::redirect($return_url);
                    //     break;
                    // }
    		    } 
        		else 
        		{
        		    echo "Order not validate";
        		}
    		}
    		else
    		{
    		    echo "Tran Id or Val Id missing";
    		    exit;
    		}
        }
        elseif($order_status == 2)
        {
            Tools::redirect($success_URL);
        }
        elseif($_POST['status'] == 'FAILED')
        {
            $history->changeIdOrderState(8, (int)($objOrder->id));
            Tools::redirect($failed_URL);
        }
        elseif($_POST['status'] == 'CANCELLED')
        {
            $history->changeIdOrderState(6, (int)($objOrder->id));
            // echo $objOrder->id;
            Tools::redirect($failed_URL);
        }
        
	}
}
