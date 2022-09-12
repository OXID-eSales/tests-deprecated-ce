<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Acceptance;

use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Facts\Edition\EditionSelector;
use OxidEsales\TestingLibrary\TestSqlPathProvider;

abstract class AcceptanceTestCase extends \OxidEsales\TestingLibrary\AcceptanceTestCase
{
    protected $preventModuleVersionNotify = true;

    protected function setUp(): void
    {
        parent::setUp();
        $this->activateTheme('azure');

        //Suppress check for new module versions on every admin login
        if ($this->preventModuleVersionNotify) {
            $aParams = array("type" => "bool", "value" => true);
            $this->callShopSC("oxConfig", null, null, array('preventModuleVersionNotify' => $aParams));
        }

        $this->activateTheme('azure');
        $this->clearCache();
    }

    /**
     * @inheritDoc
     */
    public function addTestData($testSuitePath)
    {
        $testSuitePath = realpath(
            (new TestSqlPathProvider(new EditionSelector(), $this->getTestConfig()->getShopPath()))
                ->getDataPathBySuitePath($testSuitePath)
        );
        $shopEdition = $this->getTestConfig()->getShopEdition();
        if ($shopEdition === 'EE' && !\file_exists("$testSuitePath/demodata_{$shopEdition}.sql")) {
            $this->importSql("$testSuitePath/demodata_PE_CE.sql");
        }

        parent::addTestData($testSuitePath);

        Registry::getConfig()->reinitialize();
    }
}
