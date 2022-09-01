# OXID eShop deprecated tests

These are deprecated tests, that after refactoring will be removed. Please 
do not add any new tests here

These tests are using OXID eShop testing library to test OXID eShop:
https://github.com/OXID-eSales/testing_library

## Installation

Run the following commands to install tests and the Testing library:

```bash
composer require oxid-esales/tests-deprecated-ce --with-all-dependencies
```

## Configuration

Please copy the testing configuration file ``test_config.yml.dist`` into shop root directory and
rename it to ``test_config.yml``:

```bash
cp vendor/oxid-esales/testing-library/test_config.yml.dist test_config.yml
``` 
