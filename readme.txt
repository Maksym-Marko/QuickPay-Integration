WordPress Hotel Booking Plugin (https://motopress.com/products/hotel-booking/) and QuickPay (https://quickpay.net/) payment gateway integration.

To add QuickPay to the Hotel Booking Plugin you need do next:
Activate the Hotel Booking Plugin
Add the QuickPay folder and quickpay-gateway.php to \plugins\motopress-hotel-booking\includes\payments\gateways
Open the \plugins\motopress-hotel-booking\includes\payments\gateways\gateway-manager.php file and call the QuickPay class “new QuickpayGateway();” in initPrebuiltGateways() function.
That's it. You’ll find the new payment gateway in your admin panel (/wp-admin/admin.php?page=mphb_settings&tab=payments&subtab=quickpay).

Enjoy!)
