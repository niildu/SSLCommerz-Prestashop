# SSLCommerz - Prestashop

This module work on Prestashop V1.5-1.6

#### Prerequisite

  - TLS V1.2(For Sandbox API)
  - [Sandbox Account](https://developer.sslcommerz.com/registration/ "SSLCommerz Sandbox Registration")

### INTEGRATION:

1. Unzip the module to a temporary location on your computer
2. Copy the “modules” folder in the archive to your base “PrestaShop” folder
- This should NOT overwrite any existing files or folders and merely supplement them with the SSLCommerz files
- This is however, dependent on the FTP program you use
3. Using your FTP program, ensure that the /modules as well as /modules/sslcommerz directories are set to CHMOD 0755, otherwise the validation script will not be accessible to set successful payments as paid.
4. Login to the PrestaShop Back Office console
5. Using the top navigation bar, navigate to Modules
6. Click on Payments & Gateways to expand the options
7. Click on the “Install” button to install the module
8. Once the module is installed, click on “Configure” below the SSL Commerz name.
9. The SSL Commerz options will then be shown, and you will see the module is ready to be tested.
10. Leave everything as per default and click “Save”

### How can I test that it is working correctly?

In order to Test this module, follow the instructions below:

1. Login to the PrestaShop Back Office
2. Using the top navigation bar, navigate to Modules
3. Click on Payments & Gateways to expand the options
4. Under SSLCommerz, click on the “Configure” link
5. In the SSLCommerz Settings block, use the following settings:
6. Mode = “Test”
7. Store ID = "Your Test Store ID email Via SSLCommerz"
8. Store Password = "Your Test Store Password email Via SSLCommerz"
10. Click Save


### I’m ready to go live! What do I do?
In order to make the module “LIVE”, follow the instructions below:

1. Login to the PrestaShop Back Office
2. Using the top navigation bar, navigate to Modules
3. Click on Payments & Gateways to expand the options
4. Under SSLCommerz, click on the “Configure” link
5. In the SSLCommerz Settings block, use the following settings:
6. Mode = “Live”
7. Store ID = "Your Live Store ID email Via SSLCommerz"
8. Store Password = "Your Live Store Password email Via SSLCommerz"
9. Debugging = Unchecked
10. Click Save

- Author : SSLCOMMERZ
- Team Email: integration@sslcommerz.com (For any query)
