W3C digitalData Layer Magento Extension
=======================================

Magento Extension to implement the W3C digitalData spec


How to install
--------------

To manually install the extension:

1. Download the extension
2. Unzip the file
3. Create a folder {Magento 2 root}/app/code/Persomi/Digitaldatalayer
4. Copy the content from the unzip folder


Enable W3C digitalData Layer (from {Magento root} folder):

1. php -f bin/magento module:enable --clear-static-content Persomi_Digitaldatalayer 
2. php -f bin/magento setup:upgrade
3. The extension should now begin installing
4. After it has been installed, click on 'Refresh' to see the changes

Note: you may need to log out and log back in to be able to access the configuration panel of this extension.


How to enable the Digital Data layer
------------------------------------

The Digital Data Layer is enabled by default. If it has been disabled, follow these steps to re-enable it:

1. Log in to the Admin Panel
2. Navigate through System -> Configuration
3. On the left pane under "W3C Digital Data Layer", click on "Configuration"
4. On the right area, make sure that the box with heading "Digital Data Layer Configuration" is expanded
4. For "Enable Digital Data Layer" select "Yes"
5. The Digital Data Layer should now be enabled


Extensions to the Digital Data Layer standard
---------------------------------------------

This plugin adds the "availability" and "stockLevel" attribute to the official DDL standard productInfo object:

```javascript
window.digitalData.product = [];
window.digitalData.product.push({
    productInfo: {
        availability: 'in stock|out of stock',
        stockLevel: 32,
    },
    category:...
    price:...
    linkedProduct:...
});
```

Note that whether these two properties are output in the digitalData object is configured in the configuration
of this plugin, "Enable Stock Exposure". You can choose not to output any stock information, only availability
or stock level counts.

Notes for extending the Extension
---------------------------------

Most changes to implement new features will be made to the following files:

DataLayer.php

* Location: /app/code/Persomi/Digitaldatalayer/Model/
* Info: Contains all of the code that extracts data from Magento's backend. The method `setDigitalDataLayer()`
  initialises all data objects.


head.phtml

* Location: /app/code/Persomi/Digitaldatalayer/view/frontend/templates/
* Info: Template file that outputs the digitalData layer as a JavaScript object.


system.xml

* Location: /app/code/Persomi/Digitaldatalayer/etc/adminhtml/
* Info: To make changes to the configuration section in the Admin Panel.


Authors
-------

[Miguel Vitorino](http://github.com/mvitorino)

[Muhammed Onu Miah](http://github.com/momiah)

[Blake Finney](http://github.com/blakefinney)

[David Henderson](http://github.com/dhendo)

[Vipul Dadhich](https://github.com/thoughtyards)

[Dinesh Kumar](https://github.com/thoughtyards)


License
-------

This work contains significant portions of the original W3C Digital
Data Layer by Triggered Messaging.

Original Copyright 2014 Triggered Messaging

Licensed under the Apache License, Version 2.0 (the "License");
you may not use this file except in compliance with the License.
You may obtain a copy of the License at

    http://www.apache.org/licenses/LICENSE-2.0

Unless required by applicable law or agreed to in writing, software
distributed under the License is distributed on an "AS IS" BASIS,
WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
See the License for the specific language governing permissions and
limitations under the License.


