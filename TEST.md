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