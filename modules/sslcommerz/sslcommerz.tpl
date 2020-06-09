<div class='sslcommerzPayNow'>
<form id='sslcommerzPayNow' action="{$data.sslcommerz_url}" method="post">
    <p class="payment_module"> 
    {foreach $data.info as $k=>$v}
        <input type="hidden" name="{$k}" value="{$v}" />
    {/foreach}  
     <a href='#' onclick='document.getElementById("sslcommerzPayNow").submit();return false;'><img alt='Pay Now With sslcommerz' title='Pay Now With sslcommerz' src="{$base_dir}modules/sslcommerz/logo_icon.png">{$data.sslcommerz_paynow_text}
      {if $data.sslcommerz_paynow_logo=='on'} <img align='{$data.sslcommerz_paynow_align}' alt='Pay Now With sslcommerz' title='Pay Now With sslcommerz' src="{$base_dir}modules/sslcommerz/logo.png">{/if}</a>
       <noscript><input type="image" src="{$base_dir}modules/sslcommerz/logo.png"></noscript>
    </p> 
</form>
</div>
<div class="clear"></div>
