<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Core;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\Theme;
use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContext;
use oxRegistry;
use stdClass;
use Webmozart\PathUtil\Path;

class UtilsViewTest extends \OxidTestCase
{
    public function setup(): void
    {
        parent::setUp();

        $theme = oxNew(Theme::class);
        $theme->load('azure');
        $theme->activate();
    }

    public function testGetTemplateDirsContainsAzure()
    {
        if ($this->getTestConfig()->getShopEdition() != 'CE') {
            $this->markTestSkipped('This test is for Community edition only.');
        }

        $expectedTemplateDirs = $this->getTemplateDirsAzure();
        $utilsView = oxNew(UtilsView::class);
        $utilsView->setAdminMode(false);

        $this->assertArraySubset($expectedTemplateDirs, $utilsView->getTemplateDirs());
    }

    public function testGetTemplateDirsOnlyAzure()
    {
        if ($this->getTestConfig()->getShopEdition() != 'CE') {
            $this->markTestSkipped('This test is for Community edition only.');
        }

        $expectedTemplateDirs = $this->getTemplateDirsAzure();
        $utilsView = oxNew(UtilsView::class);
        $utilsView->setAdminMode(false);

        $this->assertEquals($expectedTemplateDirs, $utilsView->getTemplateDirs());
    }

    public function testGetEditionTemplateDirsContainsAzure()
    {
        if ($this->getTestConfig()->getShopEdition() != 'CE') {
            $this->markTestSkipped('This test is for Community edition only.');
        }

        $shopPath = $this->getShopPath();

        $dirs = [
            $shopPath . 'Application/views/azure/tpl/',
            $shopPath . 'out/azure/tpl/',
        ];

        $utilsView = oxNew(UtilsView::class);
        $utilsView->setAdminMode(false);

        $result = $utilsView->getTemplateDirs();

        $this->assertArraySubset($result, $dirs);
    }

    public function testGetEditionTemplateDirsOnlyAzure()
    {
        if ($this->getTestConfig()->getShopEdition() != 'CE') {
            $this->markTestSkipped('This test is for Community edition only.');
        }

        $shopPath = $this->getShopPath();

        $dirs = [
            $shopPath . 'Application/views/azure/tpl/',
            $shopPath . 'out/azure/tpl/',
        ];

        $utilsView = oxNew(UtilsView::class);
        $utilsView->setAdminMode(false);

        $this->assertEquals($dirs, $utilsView->getTemplateDirs());
    }

    public function testGetEditionTemplateDirsForAdminContainsAzure()
    {
        if ($this->getTestConfig()->getShopEdition() != 'CE') {
            $this->markTestSkipped('This test is for Community edition only.');
        }

        $shopPath = $this->getShopPath();

        $dirs = [
            $shopPath . 'Application/views/admin/tpl/',
        ];

        $utilsView = oxNew(UtilsView::class);
        $utilsView->setAdminMode(true);

        $result = $utilsView->getTemplateDirs();

        $this->assertArraySubset($result, $dirs);
    }

    public function testGetEditionTemplateDirsForAdminOnlyAzure()
    {
        if ($this->getTestConfig()->getShopEdition() != 'CE') {
            $this->markTestSkipped('This test is for Community edition only.');
        }

        $shopPath = $this->getShopPath();

        $dirs = [
            $shopPath . 'Application/views/admin/tpl/',
        ];

        $utilsView = oxNew(UtilsView::class);
        $utilsView->setAdminMode(true);

        $this->assertEquals($dirs, $utilsView->getTemplateDirs());
    }

    public function testSetTemplateDirContainsAzure()
    {
        if ($this->getTestConfig()->getShopEdition() != 'CE') {
            $this->markTestSkipped('This test is for Community edition only.');
        }

        $myConfig = $this->getConfig();
        $aDirs[] = "testDir1";
        $aDirs[] = "testDir2";
        $aDirs[] = $myConfig->getTemplateDir(false);
        $sDir = $myConfig->getOutDir(true) . $myConfig->getConfigParam('sTheme') . "/tpl/";
        if (!in_array($sDir, $aDirs)) {
            $aDirs[] = $sDir;
        }

        $sDir = $myConfig->getOutDir(true) . "azure/tpl/";
        if (!in_array($sDir, $aDirs)) {
            $aDirs[] = $sDir;
        }

        $utilsView = oxNew(UtilsView::class);
        $utilsView->setAdminMode(false);
        $utilsView->setTemplateDir("testDir1");
        $utilsView->setTemplateDir("testDir2");
        $utilsView->setTemplateDir("testDir1");

        $result = $utilsView->getTemplateDirs();

        $this->assertArraySubset($result, $aDirs);
    }

    public function testSetTemplateDirOnlyAzure()
    {
        if ($this->getTestConfig()->getShopEdition() != 'CE') {
            $this->markTestSkipped('This test is for Community edition only.');
        }

        $myConfig = $this->getConfig();
        $aDirs[] = "testDir1";
        $aDirs[] = "testDir2";
        $aDirs[] = $myConfig->getTemplateDir(false);
        $sDir = $myConfig->getOutDir(true) . $myConfig->getConfigParam('sTheme') . "/tpl/";
        if (!in_array($sDir, $aDirs)) {
            $aDirs[] = $sDir;
        }

        $sDir = $myConfig->getOutDir(true) . "azure/tpl/";
        if (!in_array($sDir, $aDirs)) {
            $aDirs[] = $sDir;
        }

        $utilsView = oxNew(UtilsView::class);
        $utilsView->setAdminMode(false);
        $utilsView->setTemplateDir("testDir1");
        $utilsView->setTemplateDir("testDir2");
        $utilsView->setTemplateDir("testDir1");

        $this->assertEquals($aDirs, $utilsView->getTemplateDirs());
    }

    /**
     * Testing template processign code + skipped debug output code
     */
    public function testGetTemplateOutput()
    {
        $this->getConfig()->setConfigParam('iDebug', 0);
        $sTpl = __DIR__ . "/../testData//misc/testTempOut.tpl";

        $oView = oxNew('oxview');
        $oView->addTplParam('articletitle', 'xxx');

        $oUtilsView = oxNew('oxutilsview');

        $this->assertEquals('xxx', $oUtilsView->getTemplateOutput($sTpl, $oView));
    }

    public function testPassAllErrorsToView()
    {
        $aView = [];
        $aErrors[1][2] = serialize("foo");
        \OxidEsales\Eshop\Core\Registry::getUtilsView()->passAllErrorsToView($aView, $aErrors);
        $this->assertEquals($aView['Errors'][1][2], "foo");
    }

    public function testAddErrorToDisplayCustomDestinationFromParam()
    {
        $session = $this->getMock(\OxidEsales\Eshop\Core\Session::class, ['getId']);
        $session->expects($this->once())->method('getId')->will($this->returnValue(true));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Session::class, $session);

        $oxUtilsView = oxNew(\OxidEsales\Eshop\Core\UtilsView::class);
        $oxUtilsView->addErrorToDisplay("testMessage", false, true, "myDest");

        $aErrors = oxRegistry::getSession()->getVariable('Errors');
        $oEx = unserialize($aErrors['myDest'][0]);
        $this->assertEquals("testMessage", $oEx->getOxMessage());
        $this->assertNull(oxRegistry::getSession()->getVariable('ErrorController'));
    }

    public function testAddErrorToDisplayCustomDestinationFromPost()
    {
        $this->setRequestParameter('CustomError', 'myDest');
        $this->setRequestParameter('actcontrol', 'oxwminibasket');

        $session = $this->getMock(\OxidEsales\Eshop\Core\Session::class, ['getId']);
        $session->expects($this->once())->method('getId')->will($this->returnValue(true));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Session::class, $session);

        $oxUtilsView = oxNew(\OxidEsales\Eshop\Core\UtilsView::class);
        $oxUtilsView->addErrorToDisplay("testMessage", false, true, "");
        $aErrors = Registry::getSession()->getVariable('Errors');
        $oEx = unserialize($aErrors['myDest'][0]);
        $this->assertEquals("testMessage", $oEx->getOxMessage());
        $aErrorController = Registry::getSession()->getVariable('ErrorController');
        $this->assertEquals("oxwminibasket", $aErrorController['myDest']);
    }

    public function testAddErrorToDisplayDefaultDestination()
    {
        $this->setRequestParameter('actcontrol', 'start');
        $session = $this->getMock(\OxidEsales\Eshop\Core\Session::class, ['getId']);
        $session->expects($this->once())->method('getId')->will($this->returnValue(true));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Session::class, $session);

        $oxUtilsView = oxNew(\OxidEsales\Eshop\Core\UtilsView::class);
        $oxUtilsView->addErrorToDisplay("testMessage", false, true, "");
        $aErrors = Registry::getSession()->getVariable('Errors');
        $oEx = unserialize($aErrors['default'][0]);
        $this->assertEquals("testMessage", $oEx->getOxMessage());
        $aErrorController = Registry::getSession()->getVariable('ErrorController');
        $this->assertEquals("start", $aErrorController['default']);
    }

    public function testAddErrorToDisplayUsingExeptionObject()
    {
        $oTest = oxNew('oxException');
        $oTest->setMessage("testMessage");

        $session = $this->getMock(\OxidEsales\Eshop\Core\Session::class, ['getId']);
        $session->expects($this->once())->method('getId')->will($this->returnValue(true));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Session::class, $session);

        $oxUtilsView = oxNew(\OxidEsales\Eshop\Core\UtilsView::class);
        $oxUtilsView->addErrorToDisplay($oTest, false, false, "");

        $aErrors = Registry::getSession()->getVariable('Errors');
        $oEx = unserialize($aErrors['default'][0]);
        $this->assertEquals("testMessage", $oEx->getOxMessage());
    }

    public function testAddErrorToDisplayIfNotSet()
    {
        $session = $this->getMock(\OxidEsales\Eshop\Core\Session::class, ['getId']);
        $session->expects($this->once())->method('getId')->will($this->returnValue(true));
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Session::class, $session);

        $oxUtilsView = oxNew(\OxidEsales\Eshop\Core\UtilsView::class);
        $oxUtilsView->addErrorToDisplay(null, false, false, "");

        $aErrors = Registry::getSession()->getVariable('Errors');
        $this->assertFalse(isset($aErrors['default'][0]));
        $this->assertNull(Registry::getSession()->getVariable('ErrorController'));
    }

    public function testAddErrorToDisplay_startsSessionIfNotStarted()
    {
        $session = $this->getMock(\OxidEsales\Eshop\Core\Session::class, ['getId', 'isHeaderSent', 'setForceNewSession', 'start']);
        $session->expects($this->once())->method('getId')->will($this->returnValue(false));
        $session->expects($this->once())->method('isHeaderSent')->will($this->returnValue(false));
        $session->expects($this->once())->method('setForceNewSession');
        $session->expects($this->once())->method('start');
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Session::class, $session);

        $oxUtilsView = oxNew(\OxidEsales\Eshop\Core\UtilsView::class);
        $oxUtilsView->addErrorToDisplay(null, false, false, "");
    }

    private function assertArraySubset(array $subset, array $array): void
    {
        $replaced = \array_replace_recursive($array, $subset);
        $this->assertSame(
            $array,
            $replaced,
            sprintf(
                "Failed asserting that %s has the subset %s",
                \var_export($array, true),
                \var_export($subset, true)
            )
        );
    }

    /**
     * @return array
     */
    private function getTemplateDirsAzure()
    {
        $config = $this->getConfig();
        $dirs = [];
        $dirs[] = $config->getTemplateDir(false);
        $dir = $config->getOutDir(true) . $config->getConfigParam('sTheme') . "/tpl/";
        if (!in_array($dir, $dirs)) {
            $dirs[] = $dir;
        }
        $dir = $config->getOutDir(true) . "azure/tpl/";
        if (!in_array($dir, $dirs)) {
            $dirs[] = $dir;
        }
        return $dirs;
    }

    /**
     * @return string
     */
    private function getShopPath()
    {
        $config = $this->getConfig();
        $shopPath = rtrim($config->getConfigParam('sShopDir'), '/') . '/';
        return $shopPath;
    }

    /**
     * @return string
     */
    private function getCompileDirectory()
    {
        return (new BasicContext())->getCacheDirectory();
    }
}
