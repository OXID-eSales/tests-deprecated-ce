<?php

use OxidEsales\EshopCommunity\Tests\FixtureHelper;
use OxidEsales\TestingLibrary\ServiceCaller;
use OxidEsales\TestingLibrary\TestConfig;

$serviceCaller = new ServiceCaller();
$testConfig = new TestConfig();

$testDirectory = $testConfig->getEditionTestsPath($testConfig->getShopEdition());
$serviceCaller->setParameter('importSql', '@' . $testDirectory . '/Fixtures/testdata.sql');
$serviceCaller->callService('ShopPreparation', 1);

FixtureHelper::copyLegacyFixturesToTheShopsOutDirectory();

define('oxADMIN_LOGIN', oxDb::getDb()->getOne("select OXUSERNAME from oxuser where oxid='oxdefaultadmin'"));
define('oxADMIN_PASSWD', getenv('oxADMIN_PASSWD') ? getenv('oxADMIN_PASSWD') : 'admin');
