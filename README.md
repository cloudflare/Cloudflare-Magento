[![Build Status](https://travis-ci.org/cloudflare/Cloudflare-Magento.svg?branch=master)](https://travis-ci.org/cloudflare/Cloudflare-Magento)
## Installing the Cloudflare Magento2 extension
From the magento2 root directory run the following commands:

1. `composer require cloudflare/cloudflare-magento`
2. `composer update`
3. `bin/magento setup:upgrade`
4. `bin/magento setup:di:compile`

## Tests

`vendor/phpunit/phpunit/phpunit -c dev/tests/unit/phpunit.xml.dist vendor/cloudflare/cloudflare-magento/Test/Unit/`
