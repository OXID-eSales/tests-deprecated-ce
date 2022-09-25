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
        parent::addTestData($testSuitePath);

        $shopEdition = $this->getTestConfig()->getShopEdition();
        if ($shopEdition === 'EE') {
            $pathToDemoDataInEnterpriseTestsRepository = (new TestSqlPathProvider(
                new EditionSelector(),
                $this->getTestConfig()->getShopPath()
            ))
                ->getDataPathBySuitePath($testSuitePath);
            $this->importSql("$pathToDemoDataInEnterpriseTestsRepository/demodata_EE.sql");
            if ($this->getTestConfig()->isSubShop()) {
                $this->importSql("$pathToDemoDataInEnterpriseTestsRepository/demodata_EE_mall.sql");
            }
        }
        Registry::getConfig()->reinitialize();
    }
}
