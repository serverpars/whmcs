# Hostcontrol WHMCS Module

## Table of contents
- Installation instructions
- Changelog
- Frequently Asked Questions
- Known issues

## Installation instructions
1. Unzip directory hostcontrol
2. Upload directory hostcontrol to /modules/registrars/ on the WHMCS server
3. Access your WHMCS Admin Area
4. Use the navigation menu to go to “Setup -> Domain Registrars”
5. WHMCS detected the HostControl registrar module
6. Click the Activate button
7. Insert your API key and click Save

## Changelog
### February 18, 2014
- Added new module configuration option: "Use Alternative Connect Port"
-- This will cause the module to connect to port 14739 and may resolve 'no response received' messages.
-- If your server is in Iran, please use this setting!
- Added version detection. This allows you to quickly see if your module version is up-to-date. Go to “Setup -> Domain Registrars” to see if you are using the latest version.

### December 9, 2013
- Domains are now always treated as lowercase, preventing matching errors

### November 29, 2013
- Emailaddresses are now always treated as lowercase
- Edited module descriptions and texts
- Added certificate for cURL to confirm Hostcontrol SSL certificate

### November 18, 2013
- Fixed address line import
- Modified include paths, which triggered errors on some PHP configurations. The getvalidlanguages() redeclare error is now resolved

### November 15, 2013
- Fixed registration IP issue
- When registering a new domain, your default nameservers will be used instead of the Hostcontrol nameservers
PLEASE NOTE: This does currently NOT work with transfer, because they are treated differently in the Hostcontrol Backoffice. Default nameservers on transfer will be added later.

### November 13, 2013
- cURL tweaking to prevent No response received

### November 12, 2013
- Eliminated ‘no admin user found’ error
- Throws a user-friendly error when an emailaddress could not be found
- Added more FAQs and a Known issues in this readme
- Updated API URL

### November 9, 2013
- Added more debug functionality

### November 1, 2013
- Looks up existing/imported customers based on emailaddress

### Late October, 2013
- Support for
-- customer creation
-- domain registrations
-- domain transfers
-- epp-code
-- dns change
-- name server change

## Frequently Asked Questions
### Wat does the unknown label error mean?
This means that you don’t have the correct API key inserted in the registrar module configuration.

### Where do I find the API key in the HostControl backoffce?
Login to your Reseller Area. From the navigation menu, mouse-over “Dashboard” and select "Label". This will take you to the Label Overview page. Your API Key is located at the bottom of this Label Overview page.

### Why do I get the message State: Select a valid choice. That choice is not one of the available choices.?
The HostControl backend has a strict validation of countries, states and cities. Please check your clients profile and make sure the country has been set correctly and that the statename and cityname are spelled correctly. Please double-check for typing errors.

### Do I have to link customers from WHMCS to HostControl?
No. If you register a new domain for an already existing WHMCS-customer, the HostControl API will see if your HostControl account already contains a customer with the same e-mailaddress. If so, your new domain will be linked to that customer. If not, a new customer will be created in HostControl Backoffice.

### My orders aren’t being processed by HostControl Backoffice, what is happening?
If you enabled invoicing for your Storefront, HostControl Backoffice will not deliver an order before the invoice has been paid for. We recommend that you disable invoicing for your Storefront. You can do this by logging in to your Reseller Area, then go to Storefront. At the navigation menu hover Setttings, and choose General from the dropdown. On the next page click the Invoicing tab and uncheck the checkbox for “Is invoicing enabled”. If this does not solve the issue please contact us.

### No new customer account could be created, what can I do?
Please ensure that you have setup ‘accepted countries’. You can do this by logging in to your Reseller Area, then go to Storefront. At the navigation menu hover Setttings, and choose General from the dropdown. On the next page click the Customers tab. Please enable all countries you want customer to be registering from. This also applies to the Country0-setting from your WHMCS installation. If this does not solve the issue please contact us.

### My question is not listed, can I contact you guys?
Ofcourse you can. But it might be useful to check some settings in the Reseller Area first. For example, if you have problems registering domains, please check the TLD plans in your Storefront configuration. Also check if you have setup pricing for your products.

If you have any other questions, please contact us from your Reseller Area.

## Known issues
- Auth-codes on domains which are waiting for auth-code
- Auth-codes can only be resupplied by admins. We have tried to build this into the WHMCS-clientarea. Currently, - - WHMCS only lets us create WHMCS-clientarea addons for ACTIVE domains. So pending-transfer/waiting for auth-code transfer can not be modified by the client until the transfer is completed. The current solution is that the WHMCS admin, when receiving the (correct) auth-code, supplies the correct

The default nameservers you have set in the General Domain settings in WHMCS Administration panel are currently only set on newly registered domains. They will only be updated if the nameservers are: - not childnameservers - reachable on IP basis

Please refer to the Module log if nameservers have not been set, and supply the module log when opening a Support Ticket at Hostcontrol
