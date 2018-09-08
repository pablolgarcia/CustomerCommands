# Customer commands for Magento2 - by Rapicart
## Introduction
Magento 2 commands are a powerful tool and a fast way to manage our projects on our daily running.
This is why we decided to create this module, extending the out-of-the-box magento commands, providing
to the user with helpful commands to manage customer accounts.
This module is mainly aimed for sysadmins and devs who want to manage customer accounts by command line.

**Note:** This extension is still under development. Feel free to contact us for issues or suggestions.

## Installation
You need to install it using composer. Go to your web root directory and type:
```
composer require "rapicart/customer-commands":"@dev"
```
Then you need to run the next commands:
```
php bin/magento module:enable Rapicart_CustomerCommands
php bin/magento setup:upgrade
```

## Available commands
For managing customers
```
php bin/magento customer:create (Create or update a customer account)
php bin/magento customer:delete (Delete a customer account)
php bin/magento customer:info (Shows customer account details)
php bin/magento customer:password:update (Update password for a given customer)
```
For managing customer addresses
```
php bin/magento customer:address:create (Not implemented yet)
php bin/magento customer:address:delete (Delete a customer address)
php bin/magento customer:address:list (Displays the list of customer addresses)
```
For managing customer groups
```
php bin/magento customer:group:create (Create a customer group)
php bin/magento customer:group:delete (Delete a customer group)
php bin/magento customer:group:list (Displays the list of customer groups)
```