# Magento 2 extension for Inxmail Professional
This repository contains the Inxmail extension code for Magento 2

## Description

This extension requires an Inxmail Professional account and a corresponding Inxmail Professional REST-API user.

Please contact your Inxmail account manager or get in [touch with us](https://www.inxmail.de/kontakt) to get all information regarding this extension.


## Contribution

You are welcome to contribute to the ongoing development of this extension! Your possibilities:

* **Security issues**: Please do **NOT** open a github issue. Get in touch with us to report (see above) such issues.

* Report a bug: create an [issue](https://github.com/Inxmail/inx_magento2/issues/new) including description, reproduction steps, Magento and extension version numbers
* Fix a bug: please fork and use our contribution branch to submit your Pull Request
* Request a feature: create an [issue](https://github.com/Inxmail/inx_magento2/issues/new) describing your desired feature.

Thank you!


## Version History
**V1.3.2**
* Corrected incompatibilities with php 8.4
* Tested on Magento2 CE v2.4.8-p3 with PHP 8.3 and 8.4

**V1.3.1**
* Tested compatibility with Magento2 CE v2.4.7-p3 and php 8.3
* Tested compatibility with Magento2 CE v2.4.8

**V1.3.0**
* Replaced ZEND_HTTP Framework for Compatibility with Magento2 CE v2.4.6

**V1.2.0**
* Compatibility check and updates for Magento2 CE v2.4.4
* Compatibility check for PHP Version 8.1

**V1.1.4**
* compatibility check and updates for Magento2 CE v2.4.3-p1

**V1.1.0**
* added support for "last order date"

**V1.0.3**
* allow PHP > 7.1
* Workaround for Invalid header line detected: #49
* add fallback for different M2 versions (serializer)

**V1.0.2**
* Compatibility for Magento 2.2.6
* Compatibility for PHP 7.1

**V1.0.0**

This is the base-version of the module with the following feature-set
* connect your Magento 2 installation with one Inxmail Professional list
* use the full opt-in features of Inxmail Professional
* synchronize bi-directional in real-time the opt-in and opt-out of the newsletter 
* synchronize customer data regularly to Inxmail Professinal
* reduce the editorial work of your newsletters by incorporating product information directly from your shop data 


## Running the tests

The module ships with a small test suite under [Test/Unit/](Test/Unit/). Despite the name, only one of the files is a true unit test — the rest are integration tests that require a running Magento instance and valid Inxmail Professional API credentials configured in the Magento admin (`Stores → Configuration → Inxmail`).

The tests are intended to be executed from inside a Magento installation that has this module checked out at `app/code/Flagbit/Inxmail/`. Both PHPUnit and the Magento test bootstrap come from the parent Magento project — this module's `composer.json` only declares the version constraint.

### Prerequisites on the test environment

1. A working Magento 2.4.x installation (e.g. on your DigitalOcean droplet) with PHP 8.1+
2. This module installed at `<magento-root>/app/code/Flagbit/Inxmail/` (either via `git clone` directly into that path, or via Composer with a path repository)
3. Module enabled and Magento setup run:
   ```bash
   bin/magento module:enable Flagbit_Inxmail
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   ```
4. Magento dev-dependencies installed (this pulls in PHPUnit):
   ```bash
   composer install
   ```
5. For the integration tests: a valid Inxmail Professional REST API user configured under `Stores → Configuration → Inxmail → General` (API URL, username, password) and a list whose ID matches the one used in the test (see warnings below)

### Running only the real unit test

From the Magento root:
```bash
vendor/bin/phpunit app/code/Flagbit/Inxmail/Test/Unit/Model/Config/SystemConfigTest.php
```
This one needs no external services.

### Running the full suite

From inside the module's `Test/Unit/` directory (the bootstrap path in `phpunit.xml` is relative to that location):
```bash
cd app/code/Flagbit/Inxmail/Test/Unit
../../../../../../vendor/bin/phpunit
```

Or by referencing the config explicitly from the Magento root:
```bash
vendor/bin/phpunit -c app/code/Flagbit/Inxmail/Test/Unit/phpunit.xml
```

The `phpunit.xml` defines two suites:
- `Flagbit Inxmail Model` — only the `Model/` tests
- `Flagbit Inxmail All` — everything under `Test/Unit/`

Select one with `--testsuite "Flagbit Inxmail Model"`.

### Caveats / known issues with the integration tests

- **`RequestImportsTest`** uses a hard-coded list ID (`$testListId = 7`). It will only pass if a list with that exact ID exists in your Inxmail account. Edit the value or replace it with a list ID from your test environment before running.
- **`RequestListsTest`** runs a full create → read → update → delete lifecycle on a list named `test-x` and shares state across test methods via `self::$testListId`. Run the methods in declared order (PHPUnit's default).
- **`ApiClientTest`** is mostly pure logic but uses Reflection to inspect protected properties; if internals are renamed in the production code, those assertions need to be updated.

### Recommended smoke-test workflow after a Magento version bump

```bash
# from Magento root
vendor/bin/phpunit -c app/code/Flagbit/Inxmail/Test/Unit/phpunit.xml \
    --testsuite "Flagbit Inxmail All"
```
A green run gives you reasonable confidence that the API client, list CRUD and recipient endpoints still work end-to-end against the live Inxmail API.


## Licence Information
This module is licensed under OSL 3.0.
http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
Please see LICENSE.txt for the full text of the OSL 3.0 license.
