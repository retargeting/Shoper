# Important v3 release notice

**Before proper integration** make sure the DB contains the following (v3) fields:
* qs_variation
* qs_add_to_wishlist
* qs_product_images
* qs_review
* qs_price
* qs_old_price

*Also, check the App's Settings in the Publishing Administration Area.*
(https://panel.shoper.pl/default/appstore/viewApp/code/21e6f0c7c42542af3bbf267cfde9763a)



# Retargeting App for Shoper

### Contents
* Requirements & Compatibility.
* Installing Retargeting Extension.
* Configuration.
* Troubleshooting & Support.

### Requirements & Compatibility
* A Retargeting account
* A Shoper store

### Installing Retargeting Extension.
to be added..

### Configuration
The extension’s settings are available via Applications - My Applications - Retargeting Tracker - Configuration.
* Setup Domain API Key
 * Go to your Retargeting Account -> https://retargeting.biz/login
 * Get the Domain API Key from Settings Page
 * Select & copy Discounts API Key
 * Go to your Shoper Store Admin Panel - Applications - My Applications - Retargeting Tracker - Configuration
 * Paste Domain API Key
 * Click Save

* Setup Discount API Key
 * Go to your Retargeting Account -> https://retargeting.biz/login
 * Get the Discount API Key from Settings Page
 * Select & copy Discounts API Key
 * Go to your Shoper Store Admin Panel - Applications - My Applications - Retargeting Tracker - Configuration
 * Paste Discount API Key
 * Click Save

* To enable the Retargeting Application just press Enable from under App Status. After that the App Status should be set to Running

* Product Feed
 * The Product Feed is found at this URL: https://retargeting.biz/Shoper/feed.php?method=products&shop=::your-shop-url::, where ::your-shop-url:: represents the URL address of your Shop (For example: devshop-63421.shoparena.pl).

* Setup Help Pages
 * Go to your Shoper Store Admin Panel - Applications - My Applications - Retargeting Tracker - Configuration
 * Now add the URL handles for the pages on which you want the "visitHelpPage" event to fire. Use a comma as a separator for listing multiple handles. For example: http://yourshop.com/about-us is represented by the "about-us" handle
 * Click Save

* Javascript Query Selectors [Experimental]
 * The Retargeting App should work out of the box for most themes. But, as themes implementation can vary, in case there would be any problems with events not working as expected you can modify the following settings to make sure the events are linked to the right theme elements.

### Troubleshooting & Support
For help or more info, please contact us at [info@retargeting.biz](info@retargeting.biz) or visit our [website](https://retargeting.biz)
