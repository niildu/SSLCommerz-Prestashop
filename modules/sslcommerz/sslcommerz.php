<?php
/**
 * sslcommerz.php
 *
 * Copyright (c) 2016 SSLWireless
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
 * @author     JM Redwan<me@jmredwan.com>
 * @version    1.0.0
 * @date       30/05/2016
 * 
 * @copyright  2016 SSLWireless
 * @license    http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link       http://www.sslcommerz.com.bd/
 */

if (!defined('_PS_VERSION_'))
    exit;

class sslcommerz extends PaymentModule
{
    const LEFT_COLUMN = 0;
    const RIGHT_COLUMN = 1;
    const FOOTER = 2;
    const DISABLE = -1;
    
    public function __construct()
    {
        $this->name = 'sslcommerz';
        $this->tab = 'payments_gateways';
        $this->version = '1.0.0';  
        $this->currencies = true;
        $this->currencies_mode = 'radio';
        
        parent::__construct();       
       
        $this->author  = 'sslcommerz';
        $this->page = basename(__FILE__, '.php');

        $this->displayName = $this->l('sslcommerz');
        $this->description = $this->l('Accept payments by credit card, EFT and cash from both local and international buyers, quickly and securely with sslcommerz.');
        $this->confirmUninstall = $this->l('Are you sure you want to delete your details ?');
 
        

        /* For 1.4.3 and less compatibility */
        $updateConfig = array('PS_OS_CHEQUE' => 1, 'PS_OS_PAYMENT' => 2, 'PS_OS_PREPARATION' => 3, 'PS_OS_SHIPPING' => 4, 'PS_OS_DELIVERED' => 5, 'PS_OS_CANCELED' => 6,
                      'PS_OS_REFUND' => 7, 'PS_OS_ERROR' => 8, 'PS_OS_OUTOFSTOCK' => 9, 'PS_OS_BANKWIRE' => 10, 'PS_OS_PAYPAL' => 11, 'PS_OS_WS_PAYMENT' => 12);
        foreach ($updateConfig as $u => $v)
            if (!Configuration::get($u) || (int)Configuration::get($u) < 1)
            {
                if (defined('_'.$u.'_') && (int)constant('_'.$u.'_') > 0)
                    Configuration::updateValue($u, constant('_'.$u.'_'));
                else
                    Configuration::updateValue($u, $v);
            }

    }

    public function install()
    {
        unlink(dirname(__FILE__).'/../../cache/class_index.php');
        if ( !parent::install() 
            OR !$this->registerHook('payment') 
            OR !$this->registerHook('paymentReturn') 
            OR !Configuration::updateValue('sslcommerz_MERCHANT_ID', '') 
            OR !Configuration::updateValue('sslcommerz_MERCHANT_KEY', '') 
            OR !Configuration::updateValue('sslcommerz_LOGS', '1') 
            OR !Configuration::updateValue('sslcommerz_MODE', 'test')
            OR !Configuration::updateValue('sslcommerz_PAYNOW_TEXT', 'Pay Now With')
            OR !Configuration::updateValue('sslcommerz_PAYNOW_LOGO', 'on')  
            OR !Configuration::updateValue('sslcommerz_PAYNOW_ALIGN', 'right')  )
        {            
            return false;
        }
            

        return true;
    }

    public function uninstall()
    {
        unlink(dirname(__FILE__).'/../../cache/class_index.php');
        return ( parent::uninstall() 
            AND Configuration::deleteByName('sslcommerz_MERCHANT_ID') 
            AND Configuration::deleteByName('sslcommerz_MERCHANT_KEY') 
            AND Configuration::deleteByName('sslcommerz_MODE') 
            AND Configuration::deleteByName('sslcommerz_LOGS')
            AND Configuration::deleteByName('sslcommerz_PAYNOW_TEXT') 
            AND Configuration::deleteByName('sslcommerz_PAYNOW_LOGO')            
            AND Configuration::deleteByName('sslcommerz_PAYNOW_ALIGN')
            );

    }

    public function getContent()
    {
        global $cookie;
        $errors = array();
        $html = '<div style="width:550px">
            <p style="text-align:center;"><a href="https://www.sslcommerz.com" target="_blank"><img src="'.__PS_BASE_URI__.'modules/sslcommerz/secure_logo.png" alt="sslcommerz" boreder="0" /></a></p><br />';

             

        /* Update configuration variables */
        if ( Tools::isSubmit( 'submitsslcommerz' ) )
        {
            if( $paynow_text =  Tools::getValue( 'sslcommerz_paynow_text' ) )
            {
                 Configuration::updateValue( 'sslcommerz_PAYNOW_TEXT', $paynow_text );
            }

            if( $paynow_logo =  Tools::getValue( 'sslcommerz_paynow_logo' ) )
            {
                 Configuration::updateValue( 'sslcommerz_PAYNOW_LOGO', $paynow_logo );
            }
            if( $paynow_align =  Tools::getValue( 'easyoayway_paynow_align' ) )
            {
                 Configuration::updateValue( 'sslcommerz_PAYNOW_ALIGN', $paynow_align );
            }
            
            
            $mode = ( Tools::getValue( 'sslcommerz_mode' ) == 'live' ? 'live' : 'test' ) ;
            Configuration::updateValue('sslcommerz_MODE', $mode );
            
                if( ( $merchant_id = Tools::getValue( 'sslcommerz_merchant_id' ) ) AND preg_match('/[a-zA-Z0-9]/', $merchant_id ) )
                {
                    Configuration::updateValue( 'sslcommerz_MERCHANT_ID', $merchant_id );
                }           
                else
                {
                    $errors[] = '<div class="warning warn"><h3>'.$this->l( 'Merchant ID seems to be wrong' ).'</h3></div>';
                }

                if( ( $merchant_key = Tools::getValue( 'sslcommerz_merchant_key' ) ) AND preg_match('/[a-zA-Z0-9]/', $merchant_key ) )
                {
                    Configuration::updateValue( 'sslcommerz_MERCHANT_KEY', $merchant_key );
                }
                else
                {
                    $errors[] = '<div class="warning warn"><h3>'.$this->l( 'Merchant key seems to be wrong' ).'</h3></div>';
                }                  

                if( !sizeof( $errors ) )
                {
                    //Tools::redirectAdmin( $currentIndex.'&configure=sslcommerz&token='.Tools::getValue( 'token' ) .'&conf=4' );
                }
                
           
            if( Tools::getValue( 'sslcommerz_logs' ) )
            {
                Configuration::updateValue( 'sslcommerz_LOGS', 1 );
            }
            else
            {
                Configuration::updateValue( 'sslcommerz_LOGS', 0 );
            } 
            foreach(array('displayLeftColumn', 'displayRightColumn', 'displayFooter') as $hookName)
                if ($this->isRegisteredInHook($hookName))
                    $this->unregisterHook($hookName);
            if (Tools::getValue('logo_position') == self::LEFT_COLUMN)
                $this->registerHook('displayLeftColumn');
            else if (Tools::getValue('logo_position') == self::RIGHT_COLUMN)
                $this->registerHook('displayRightColumn'); 
             else if (Tools::getValue('logo_position') == self::FOOTER)
                $this->registerHook('displayFooter'); 
            if( method_exists ('Tools','clearSmartyCache') )
            {
                Tools::clearSmartyCache();
            } 
            
        }      
        
       

        /* Display errors */
        if (sizeof($errors))
        {
            $html .= '<ul style="color: red; font-weight: bold; margin-bottom: 30px; width: 506px; background: #FFDFDF; border: 1px dashed #BBB; padding: 10px;">';
            foreach ($errors AS $error)
                $html .= '<li>'.$error.'</li>';
            $html .= '</ul>';
        }



        $blockPositionList = array(
            self::DISABLE => $this->l('Disable'),
            self::LEFT_COLUMN => $this->l('Left Column'),
            self::RIGHT_COLUMN => $this->l('Right Column'),
            self::FOOTER => $this->l('Footer'));

        if( $this->isRegisteredInHook('displayLeftColumn') )
        {
            $currentLogoBlockPosition = self::LEFT_COLUMN ;
        }
        elseif( $this->isRegisteredInHook('displayRightColumn') )
        {
            $currentLogoBlockPosition = self::RIGHT_COLUMN; 
        }
        elseif( $this->isRegisteredInHook('displayFooter'))
        {
            $currentLogoBlockPosition = self::FOOTER;
        }
        else
        {
            $currentLogoBlockPosition = -1;
        }
        
$IPNURL = Tools::getHttpHost( true ).__PS_BASE_URI__.'modules/sslcommerz/validation.php?itn_request=true';
    /* Display settings form */
        $html .= '
        <form action="'.$_SERVER['REQUEST_URI'].'" method="post">
          <fieldset>
          <legend><img src="'.__PS_BASE_URI__.'modules/sslcommerz/logo_icon.png" />'.$this->l('Settings').'</legend>
            <p>'.$this->l('Use the "Test" mode to test out the module then you can use the "Live" mode if no problems arise. Remember to insert your merchant key and ID for the live mode.').'</p>
            <label>
              '.$this->l('Mode').'
            </label>
            <div class="margin-form" style="width:110px;">
              <select name="sslcommerz_mode">
                <option value="live"'.(Configuration::get('sslcommerz_MODE') == 'live' ? ' selected="selected"' : '').'>'.$this->l('Live').'&nbsp;&nbsp;</option>
                <option value="test"'.(Configuration::get('sslcommerz_MODE') == 'test' ? ' selected="selected"' : '').'>'.$this->l('Test').'&nbsp;&nbsp;</option>
              </select>
            </div>
            <p>'.$this->l('You can find your ID and Key in your sslcommerz account > My Account > Integration.').'</p>
            <label>
              '.$this->l('Store ID').'
            </label>
            <div class="margin-form">
              <input type="text" name="sslcommerz_merchant_id" value="'.Tools::getValue('sslcommerz_merchant_id', Configuration::get('sslcommerz_MERCHANT_ID')).'" />
            </div>
            <label>
              '.$this->l('Store Password').'
            </label>
            <div class="margin-form">
              <input type="text" name="sslcommerz_merchant_key" value="'.trim(Tools::getValue('sslcommerz_merchant_key', Configuration::get('sslcommerz_MERCHANT_KEY'))).'" />
            </div> 
            
            <p>'.$this->l('You can log the server-to-server communication. The log file for debugging can be found at ').' '.__PS_BASE_URI__.'modules/sslcommerz/sslcommerz.log. '.$this->l('If activated, be sure to protect it by putting a .htaccess file in the same directory. If not, the file will be readable by everyone.').'</p>       
            <label>
              '.$this->l('Debug').'
            </label>
            <div class="margin-form" style="margin-top:5px">
              <input type="checkbox" name="sslcommerz_logs"'.(Tools::getValue('sslcommerz_logs', Configuration::get('sslcommerz_LOGS')) ? ' checked="checked"' : '').' />
            </div>
            <p>'.$this->l('During checkout the following is what the client gets to click on to pay with sslcommerz.').'</p>            
            <label>&nbsp;</label>
            <div class="margin-form" style="margin-top:5px">
                '.Configuration::get('sslcommerz_PAYNOW_TEXT');

           if(Configuration::get('sslcommerz_PAYNOW_LOGO')=='on')
            {
                $html .= '<img align="'.Configuration::get('sslcommerz_PAYNOW_ALIGN').'" alt="Pay Now With sslcommerz" title="Pay Now With sslcommerz" src="'.__PS_BASE_URI__.'modules/sslcommerz/logo.png">';
            }
            $html .='</div>
            <label>
            '.$this->l('PayNow Text').'
            </label>
            <div class="margin-form" style="margin-top:5px">
                <input type="text" name="sslcommerz_paynow_text" value="'. Configuration::get('sslcommerz_PAYNOW_TEXT').'">
            </div>
            <label>
            '.$this->l('PayNow Logo').'

            </label>
            <div class="margin-form" style="margin-top:5px">
                <input type="radio" name="sslcommerz_paynow_logo" value="off" '.( Configuration::get('sslcommerz_PAYNOW_LOGO')=='off' ? ' checked="checked"' : '').'"> &nbsp; '.$this->l('None').'<br>
                <input type="radio" name="sslcommerz_paynow_logo" value="on" '.( Configuration::get('sslcommerz_PAYNOW_LOGO')=='on' ? ' checked="checked"' : '').'"> &nbsp; <img src="'.__PS_BASE_URI__.'modules/sslcommerz/logo.png">
            </div>
            <label>
            '.$this->l('PayNow Logo Align').'
            </label>
            <div class="margin-form" style="margin-top:5px">
                <input type="radio" name="sslcommerz_paynow_align" value="left" '.( Configuration::get('sslcommerz_PAYNOW_ALIGN')=='left' ? ' checked="checked"' : '').'"> &nbsp; '.$this->l('Left').'<br>
                <input type="radio" name="sslcommerz_paynow_align" value="right" '.( Configuration::get('sslcommerz_PAYNOW_ALIGN')=='right' ? ' checked="checked"' : '').'"> &nbsp; '.$this->l('Right').'
            </div>
            <p>'.$this->l('Where would you like the the Secure Payments made with sslcommerz image to appear on your website?').'</p>
            <label>
            '.$this->l('Select the image position').'
            <label>
            <div class="margin-form" style="margin-bottom:18px;width:110px;">
                  <select name="logo_position">';
                    foreach($blockPositionList as $position => $translation)
                    {
                        $selected = ($currentLogoBlockPosition == $position) ? 'selected="selected"' : '';
                        $html .= '<option value="'.$position.'" '.$selected.'>'.$translation.'</option>';
                    }
            $html .='</select></div>

            <div style="float:right;"><input type="submit" name="submitsslcommerz" class="button" value="'.$this->l('   Save   ').'" /></div><div class="clear"></div>
          </fieldset>
        </form>
        <br /><br />
        <fieldset>
          <legend><img src="../img/admin/warning.gif" />'.$this->l('Information').'</legend>

<p><h3>- '.$this->l('Note : IPN Setup URL - ').''.$IPNURL.'</h3></p>

          <p>- '.$this->l('In order to use your sslcommerz module, you must insert your sslcommerz Store ID and Store Password above.').'</p>
          <p>- '.$this->l('Any orders in currencies other than ZAR will be converted by prestashop prior to be sent to the sslcommerz payment gateway.').'<p>
         
        </fieldset>
        </div>';
    
        return $html;
    }

    private function _displayLogoBlock($position)
    {      
        return '<div style="text-align:center;"><a href="https://www.sslcommerz.com" target="_blank" title="Secure Payments With sslcommerz"><img src="'.__PS_BASE_URI__.'modules/sslcommerz/secure_logo.png" width="150" /></a></div>';
    }

    public function hookDisplayRightColumn($params)
    {
        return $this->_displayLogoBlock(self::RIGHT_COLUMN);
    }

    public function hookDisplayLeftColumn($params)
    {
        return $this->_displayLogoBlock(self::LEFT_COLUMN);
    }  

    public function hookDisplayFooter($params)
    {
        $html = '<section id="sslcommerz_footer_link" class="footer-block col-xs-12 col-sm-2">        
        <div style="text-align:center;"><a href="https://www.sslcommerz.com" target="_blank" title="Secure Payments With sslcommerz"><img src="'.__PS_BASE_URI__.'modules/sslcommerz/secure_logo.png"  /></a></div>  
        </section>';
        return $html;
    }    

    public function hookPayment($params)
    {   
        global $cookie, $cart; 
        if (!$this->active)
        {
            return;
        }
		$cart = new Cart(intval($cookie->id_cart));
        
        // Buyer details
        $customer = new Customer((int)($cart->id_customer));
        
        $toCurrency = new Currency(Currency::getIdByIsoCode('ZAR'));
        $fromCurrency = new Currency((int)$cookie->id_currency);
        $address = new Address(intval($cart->id_address_invoice));
        $address_ship = new Address(intval($cart->id_address_delivery));
        $total = $cart->getOrderTotal();
        $currency = new Currency(intval($cart->id_currency));
		$currency_iso_code = $currency->iso_code;
        $pfAmount = Tools::convertPriceFull( $total, $fromCurrency, $toCurrency );
       
        $data = array();

        $currency = $this->getCurrency((int)$cart->id_currency);
        if ($cart->id_currency != $currency->id)
        {
            // If sslcommerz currency differs from local currency
            $cart->id_currency = (int)$currency->id;
            $cookie->id_currency = (int)$cart->id_currency;
            $cart->update();
        }
        

        // Use appropriate merchant identifiers
        // Live
        if( Configuration::get('sslcommerz_MODE') == 'live' )
        {
            $data['info']['store_id'] = Configuration::get('sslcommerz_MERCHANT_ID');
            $data['info']['signature_key'] = Configuration::get('sslcommerz_MERCHANT_KEY');
            $data['sslcommerz_url'] = 'https://securepay.sslcommerz.com/gwprocess/v3/process.php';
        }
        // Sandbox
        else
        {
            $data['info']['store_id'] = Configuration::get('sslcommerz_MERCHANT_ID');
            $data['sslcommerz_url'] = 'https://sandbox.sslcommerz.com/gwprocess/v3/process.php';
        }
        $data['sslcommerz_paynow_text'] = Configuration::get('sslcommerz_PAYNOW_TEXT');        
        $data['sslcommerz_paynow_logo'] = Configuration::get('sslcommerz_PAYNOW_LOGO');
        $data['sslcommerz_paynow_align'] = Configuration::get('sslcommerz_PAYNOW_ALIGN');
	
        // Create URLs
        $data['info']['value_a'] = $this->context->link->getPageLink( 'order-confirmation', null, null, 'key='.$cart->secure_key.'&id_cart='.(int)($cart->id).'&id_module='.(int)($this->id));
		$data['info']['value_b'] = $this->context->link->getPageLink( 'order-confirmation', null, null, 'key='.$cart->secure_key.'&id_cart='.(int)($cart->id).'&id_module='.(int)($this->id));

$data['info']['success_url'] = Tools::getHttpHost( true ).__PS_BASE_URI__.'modules/sslcommerz/validation.php?itn_request=true';
		$data['info']['fail_url'] = Tools::getHttpHost( true ).__PS_BASE_URI__.'modules/sslcommerz/validation.php?itn_request=true';

        $data['info']['cancel_url'] = Tools::getHttpHost( true ).__PS_BASE_URI__;
        $data['info']['ipn_url'] = Tools::getHttpHost( true ).__PS_BASE_URI__.'modules/sslcommerz/validation.php?itn_request=true';
		
		//AMOUNT AND CURRENCY OTHER
        $data['info']['tran_id'] = $cart->id;
        $data['info']['desc'] = Configuration::get('PS_SHOP_NAME') .' purchase, Cart Item ID #'. $cart->id; 
		$data['info']['currency'] =  $currency_iso_code;
        $data['info']['total_amount'] = number_format( sprintf( "%01.2f", $total ), 2, '.', '' );
		
		//Billing Information 
        $data['info']['cus_name'] = $customer->firstname.' '.$customer->lastname;
        $data['info']['cus_email'] = $customer->email;      
		$data['info']['cus_add1'] = $address->address1;  
		$data['info']['cus_add2'] = $address->address2;  
		$data['info']['cus_city'] = $address->city;  
		$data['info']['cus_state'] = $customer->email;  
		$data['info']['cus_postcode'] = $address->postcode;  
		$data['info']['cus_country'] = $address->country; 
		$data['info']['cus_phone'] = $address->phone; 
		
		//Shipping Information 
        $data['info']['ship_name'] = $address_ship->firstname.' '.$address_ship->lastname;
        $data['info']['ship_add1'] = $address_ship->address1;   
		$data['info']['ship_add2'] = $address_ship->address2; 
		$data['info']['ship_city'] = $address_ship->city; 
		$data['info']['ship_state'] = $customer->email; 
		$data['info']['ship_postcode'] = $address_ship->postcode;  
		$data['info']['ship_country'] = $address_ship->country; 
          
       
        $this->context->smarty->assign( 'data', $data );        
  
        return $this->display(__FILE__, 'sslcommerz.tpl'); 
    }

    public function hookPaymentReturn($params)
    {
        if (!$this->active)
        {
            return;
        }
        $test = __FILE__;

        return $this->display($test, 'sslcommerz_success.tpl');
    
    }
   
}


