## Running the tests

The module ships with a small test suite under [Test/Unit/](Test/Unit/). Despite the name, only one of the files is a true unit test (`SystemConfigTest`) — the rest are integration tests that require a running Magento instance and valid Inxmail Professional API credentials configured in the Magento admin (`Stores → Configuration → Inxmail`).

The PHPUnit binary and the Magento test framework both come from the parent Magento project's `vendor/`. This module's `composer.json` only declares the version constraint.

### Supported PHPUnit versions

The test suite is compatible with **PHPUnit 9.5+** and **PHPUnit 12.x** (Magento 2.4.9 ships PHPUnit 12). The `phpunit.xml` uses the PHPUnit 12 schema; older PHPUnit 9 versions still accept it.

### Prerequisites on the test environment

1. A working Magento 2.4.x installation (e.g. on a DigitalOcean droplet) with PHP 8.1+ (PHP 8.3+ for PHPUnit 12)
2. This module installed in one of two supported locations — both work out of the box:
   - **`vendor/inxmail/magento2-module/`** — when installed via Composer (e.g. via `install-module.sh` which configures a VCS repository against `github.com/Inxmail/inx_magento2`)
   - **`app/code/Flagbit/Inxmail/`** — when installed by cloning the repo directly into the Magento source tree
3. Module enabled and Magento setup run (the install script does this):
   ```bash
   bin/magento module:enable Flagbit_Inxmail
   bin/magento setup:upgrade
   bin/magento setup:di:compile
   ```
4. Magento dev-dependencies installed (provides PHPUnit):
   ```bash
   composer install
   ```
5. For the integration tests: a valid Inxmail Professional REST API user configured under `Stores → Configuration → Inxmail → General` (API URL, username, password). See caveats below regarding hard-coded list IDs.

### How the bootstrap finds Magento

The suite uses [Test/Unit/bootstrap.php](Test/Unit/bootstrap.php) which auto-detects whether the module lives in `vendor/` or `app/code/` and pulls in Composer's autoloader plus Magento's unit-test framework bootstrap from the right relative path. No manual path tweaking is needed.

### Running the suite

Run from the **Magento root** (`/var/www/html` in a typical install), pointing at the module's `phpunit.xml`:

```bash
# vendor install (Composer VCS / Marketplace)
vendor/bin/phpunit -c vendor/inxmail/magento2-module/Test/Unit/phpunit.xml

# app/code install
vendor/bin/phpunit -c app/code/Flagbit/Inxmail/Test/Unit/phpunit.xml
```

Two testsuites are defined:
- `Flagbit Inxmail Model` — only the `Model/` tests
- `Flagbit Inxmail All` — everything under `Test/Unit/`

Pick one with `--testsuite "Flagbit Inxmail Model"`.

### Running just the real unit test (no Magento DB / no Inxmail API needed)

```bash
# vendor install
vendor/bin/phpunit -c vendor/inxmail/magento2-module/Test/Unit/phpunit.xml \
    --filter SystemConfigTest

# app/code install
vendor/bin/phpunit -c app/code/Flagbit/Inxmail/Test/Unit/phpunit.xml \
    --filter SystemConfigTest
```

### Caveats with the integration tests

- **`RequestImportsTest`** uses a hard-coded list ID (`$testListId = 7`). It will only pass if a list with that exact ID exists in your Inxmail account. Edit the value or replace it before running.
- **`RequestListsTest`** runs a full create → read → update → delete lifecycle on a list named `test-x` and shares state across test methods via `self::$testListId`. PHPUnit's default declaration order works.
- **`ApiClientTest`** is mostly pure logic but uses Reflection to inspect protected properties; if internals get renamed in the production code, those assertions need to be updated.
- The integration tests instantiate `\Magento\Framework\App\Bootstrap` and make real HTTP calls — they require the same DB state and Inxmail connectivity as a running shop.

### Smoke-test workflow after a Magento version bump

```bash
# from Magento root, vendor install
vendor/bin/phpunit -c vendor/inxmail/magento2-module/Test/Unit/phpunit.xml \
    --testsuite "Flagbit Inxmail All"
```

A green run gives reasonable confidence that the API client, list CRUD and recipient endpoints still work end-to-end against the live Inxmail API.
