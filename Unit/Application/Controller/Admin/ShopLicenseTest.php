<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Application\Controller\Admin;

use OxidEsales\EshopCommunity\Application\Model\Shop;
use \oxDb;
use oxSystemComponentException;
use OxidEsales\EshopCommunity\Core\Exception\SystemComponentException;

/**
 * Tests for Shop_License class
 */
class ShopLicenseTest extends \OxidTestCase
{
    /**
     * Sets malladmin parameter
     *
     * @return null|void
     */
    public function setup(): void
    {
        $this->getSession()->setVariable("malladmin", true);

        parent::setUp();
    }

    /**
     * Unsets malladmin parameter
     */
    public function tearDown(): void
    {
        $this->getSession()->setVariable("malladmin", null);

        parent::tearDown();
    }

    /**
     * Shop_License::Render() test case
     *
     * @return null
     */
    public function testRenderDemoShop()
    {
        $oConfig = $this->getMock(\OxidEsales\Eshop\Core\Config::class, array("isDemoShop"));
        $oConfig->expects($this->once())->method('isDemoShop')->will($this->returnValue(true));

        // testing..
        $oView = $this->getMock(\OxidEsales\Eshop\Application\Controller\Admin\ShopLicense::class, array("getConfig"), array(), '', false);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);
        $this->expectException(SystemComponentException::class);
        $oView->render();
    }

    /**
     * Shop_License::Render() test case
     *
     * @return null
     */
    public function testRender()
    {
        $this->setRequestParameter("oxid", oxDb::getDb()->getOne("select oxid from oxshops"));

        // testing..
        $oView = oxNew('Shop_License');
        $this->assertEquals('shop_license', $oView->render());
        $aViewData = $oView->getViewData();
        $this->assertTrue(isset($aViewData['edit']));
        $this->assertTrue($aViewData['edit'] instanceof shop);
        $this->assertTrue(isset($aViewData['version']));
    }

    /**
     * UserGroup_Main::Render() test case
     *
     * @return null
     */
    public function testRenderNoRealObjectId()
    {
        $this->setRequestParameter("oxid", "-1");

        // testing..
        $oView = oxNew('Shop_License');
        $this->assertEquals('shop_license', $oView->render());
        $aViewData = $oView->getViewData();
        $this->assertTrue(isset($aViewData['oxid']));
        $this->assertEquals("-1", $aViewData['oxid']);
    }

    /**
     * Testting Shop_License::canUpdate();
     */
    public function testCanUpdate()
    {
        $oSubj = $this->getProxyClass("Shop_License");

        $this->getSession()->setVariable("malladmin", true);

        $oConfig = $this->getMock(\OxidEsales\Eshop\Core\Config::class, array("isDemoShop", "getConfigParam", "setConfigParam", "saveShopConfVar", "getBaseShopId"));
        $oConfig->expects($this->any())->method('isDemoShop')->will($this->returnValue(false));
        $oView = $this->getMock(\OxidEsales\Eshop\Application\Controller\Admin\ShopLicense::class, array("getConfig"), array(), '', false);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        $this->assertTrue($oSubj->canUpdate());
    }

    /**
     * Testting Shop_License::canUpdate(); for malladmin
     */
    public function testCanUpdateForNonMallAdmin()
    {
        $oSubj = $this->getProxyClass("Shop_License");

        $this->getSession()->setVariable("malladmin", false);

        $oConfig = $this->getMock(\OxidEsales\Eshop\Core\Config::class, array("isDemoShop", "getConfigParam", "setConfigParam", "saveShopConfVar", "getBaseShopId"));
        $oConfig->expects($this->any())->method('isDemoShop')->will($this->returnValue(false));
        $oView = $this->getMock(\OxidEsales\Eshop\Application\Controller\Admin\ShopLicense::class, array("getConfig"), array(), '', false);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        $this->assertFalse($oSubj->canUpdate());
    }

    /**
     * Testting Shop_License::canUpdate(); for demo shops (#3870)
     */
    public function testCanUpdateForDemoVersion()
    {
        $oSubj = $this->getProxyClass("Shop_License");

        $this->getSession()->setVariable("malladmin", true);

        $oConfig = $this->getMock(\OxidEsales\Eshop\Core\Config::class, array("isDemoShop"));
        $oConfig->expects($this->any())->method('isDemoShop')->will($this->returnValue(true));
        $oView = $this->getMock(\OxidEsales\Eshop\Application\Controller\Admin\ShopLicense::class, array("getConfig"), array(), '', false);
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Config::class, $oConfig);

        $this->assertFalse($oView->canUpdate());
    }
}
