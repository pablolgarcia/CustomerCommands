# Customer commands for Magento2 - by Rapicart
## Introduction
This module is mainly aimed for sysadmins who want to manage customer accounts by command line.

Still under development.
## Installation
```
composer require "rapicart/customer-commands":"@dev"
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