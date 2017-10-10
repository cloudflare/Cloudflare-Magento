[![Build Status](https://travis-ci.org/cloudflare/Cloudflare-Magento.svg?branch=master)](https://travis-ci.org/cloudflare/Cloudflare-Magento)

## Installing the Cloudflare Magento2 extension
From the magento2 root directory run the following commands:

1. `composer require cloudflare/cloudflare-magento`
2. `composer update`
3. `bin/magento setup:upgrade`
4. `bin/magento setup:di:compile`

## Versions of Magento2 supported
* Up to Magento2 CE 2.2.0

## Development
You'll need to get [authorization keys](http://devdocs.magento.com/guides/v2.0/install-gde/prereq/connect-auth.html) from the Magento marketplace and make an `auth.json`:
```
{
    "http-basic": {
        "repo.magento.com": {
            "username": "[MAGENTO USERNAME]",
            "password": "[MAGENTO PASSWORD]"
        }
    }
}
```
This will allow `composer install` to authenticate against `repo.magento.com/`.

### Development Commands
1. `composer test`
2. `composer lint`
3. `composer format`

## Tests
`vendor/phpunit/phpunit/phpunit -c dev/tests/unit/phpunit.xml.dist vendor/cloudflare/cloudflare-magento/Test/Unit/`
