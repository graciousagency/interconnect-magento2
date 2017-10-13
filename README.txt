The Gracious Studios Interconnect module for Magento 2 channels event data to the Gracious Interconnect webservice
which in turn formats the data and proxies it to connected consumer services. At this point the only connected consumer
is Copernica. More consumers will be connected in the future.

Event data is automatically channeled to the web service but the module also comes with 4 console commands to
synchronize data manually. These console commands are:
- 'interconnect:synccustomer' :     Synchronizes a customer by providing the --id={customerId} parameter
- 'interconnect:syncorder' :        Synchronizes an order by providing the --id={customerId} parameter
- 'interconnect:syncquote' :        Synchronizes a quote by providing the --id={customerId} parameter
- 'interconnect:syncsubscriber' :   Synchronizes a subscriber by providing the --id={customerId} parameter

To get the module up and running:
- Install the module using composer. It will be installed in the vendor folder.
- Run "./bin/magento setup:upgrade" (without the quotes) from the commandline in the root folder of the Magento
installation. This will install the module.
- Run "./bin/magento setup:di:compile" (without the quotes) from the commandline in the root folder of the Magento
installation. This will configure the dependency injection.
- In the backend of the webshop. go to Stores > Configuration from the main menu and click on 'General Settings'
under 'Interconnect' in the configuration menu. You will have to enter the url for the Interconnect webservice here and
provide a prefix for your application. Let's say you web shop is called 'ProShop'; your prefix could be 'PS' for
example. Now click 'Save'. The module is now configured.