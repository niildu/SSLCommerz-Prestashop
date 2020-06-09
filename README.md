# SSLCommerz - Prestashop

SSLCOMMERZ-Online Payment Gateway for Bangladesh. This Module Work for `Prestashop V 1.7.x`

### Prerequisite

  - TLS V1.2(For Sandbox API)
  - [Sandbox Account](https://developer.sslcommerz.com/registration/ "SSLCommerz Sandbox Registration")

### Installation Steps:

Please follow these steps to install the SSLCOMMERZ Payment Gateway module.

- Step 1: First Download the File from sslcommerz/Prestashop-V-1.7.x-IPN.
- Step 2: Unzip Prestashop-V-1.7.x-IPN.
- Step 3: Only Zip SSLCOMMERZ folder.
- Step 4: Upload SSLCOMMERZ.zip file to Prestashop admin panel.
- Step 5: Follow the navigation IMPROVE >> Modules >> Modules & Services >> SSLCOMMERZ (Search for SSLCOMMERZ).
- Step 6: After successful installation go to SSLCOMMERZ Configure.
- Step 7: In configuration keep Active Module Yes to active/ No to inactive.
- Step 8: Keep Live Mode Yes if you want to use your Securepay/Live Store id & Password. No for Sandbox/Test Store id & Password.
- Step 9: Set module Title, this will show to checkout page. 
- Step 10: Set your valid Merchant ID, Merchant Password provided from SSLCommerz (Mandatory). 
- Step 11: You can set additional information to Details.
- Step 12: Copy your IPN URL and set URL to your merchant panel (Auto Generated).

### Addition Information:

* This module allows you to accept secure payments by SSLCOMMERZ.
* Initially the order status will change to ‘Processing in progress’.
* Order Status (Payment Success): Should be ‘Payment accepted’ or Complete.
* Order Status (Payment Failed): Should be ‘Payment error’.
* Order Status (Payment Canceled): Should be ‘Canceled’.
* IPN URL: Find IPN URL from SSLCOMMERZ settings or http://example.com/index.php?fc=module&module=SSLCOMMERZ&controller=ipn

### Image Reference:

* Follow Step 5
![SSLCOMMERZ in Prestashop Admin Panel](images/adminpanel.png)

* Follow Step 6
![SSLCOMMERZ Configure Page](images/configure.png)

* Show In Checkout Page(Step 9)
![Checkout Page](images/checkout.png)

* Follow Step 12
![How to set IPN in your merchant panel](images/ipnset.png)

---------------------------------------------------------------------------------

- Author : Prabal Mallick
- Team Email: integration@sslcommerz.com (For any query)
- More info: https://www.sslcommerz.com

© 2018 SSLCOMMERZ ALL RIGHTS RESERVED
