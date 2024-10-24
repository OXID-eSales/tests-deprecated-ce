<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Core;

use Exception;
use modDB;
use oxField;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\EshopCommunity\Tests\ContainerTrait;
use oxRegistry;
use oxTestModules;
use oxUtils;
use stdClass;

class UtilsTest extends \OxidTestCase
{
    use ContainerTrait;

    protected $_sTestLogFileName = null;

    /**
     * Tear down the fixture.
     *
     * @return null
     */
    protected function tearDown(): void
    {
        oxRegistry::getUtils()->commitFileCache();

        clearstatcache();
        //removing test files from tmp dir
        $sFilePath = $this->getConfig()->getConfigParam('sCompileDir') . "*testFileCache*.txt";
        $aPaths = glob($sFilePath);
        if (is_array($aPaths)) {
            foreach ($aPaths as $sFilename) {
                @unlink($sFilename);
            }
        }

        if ($this->_sTestLogFileName !== null) {
            unlink($this->_sTestLogFileName);
            $this->_sTestLogFileName = null;
        }
        if (file_exists('tmp_testCacheName')) {
            unlink('tmp_testCacheName');
        }

        $oUtils = oxRegistry::getUtils();
        $sFileName = $oUtils->getCacheFilePath("testVal", false, 'php');
        if (file_exists($sFileName)) {
            unlink($sFileName);
        }

        $sFileName = $oUtils->getCacheFilePath('testCache1');
        if (file_exists($sFileName)) {
            unlink($sFileName);
        }

        parent::tearDown();
    }

    /**
     *
     * @return unknown_type
     */
    public function testExtractDomain()
    {
        $oUtils = oxNew('oxUtils');
        $this->assertEquals("oxid-esales.com", $oUtils->extractDomain("www.oxid-esales.com"));
        $this->assertEquals("oxid-esales.com", $oUtils->extractDomain("oxid-esales.com"));
        $this->assertEquals("127.0.0.1", $oUtils->extractDomain("127.0.0.1"));
        $this->assertEquals("oxid-esales.com", $oUtils->extractDomain("ssl.oxid-esales.com"));
        $this->assertEquals("oxid-esales", $oUtils->extractDomain("oxid-esales"));
    }

    public function testShowMessageAndExit()
    {
        // This Exception is used to avoid exit() in method showMessageAndExit, which would stop running tests.
        $this->expectException(
            'Exception',
            'Stop process before PHP exit() is called.'
        );
        $session = $this->getMock(\OxidEsales\Eshop\Core\Session::class, array("freeze"));
        $session->expects($this->once())->method('freeze');
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Session::class, $session);

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array("commitFileCache"));
        $oUtils->expects($this->once())
            ->method('commitFileCache')
            ->will($this->throwException(new Exception('Stop process before PHP exit() is called.')));

        $oUtils->showMessageAndExit("");
    }

    public function testSetLangCache()
    {
        $aLangCache = array("ggg" => "bbb");
        $sCacheName = 'tmp_testCacheName';

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('getCacheFilePath'));
        $oUtils->expects($this->once())->method('getCacheFilePath')->with($this->equalTo($sCacheName))->will($this->returnValue("tmp_testCacheName"));
        $oUtils->setLangCache($sCacheName, $aLangCache);
    }


    public function testgetLangCache()
    {
        $sCacheName = time();
        $aLangCache = array("ggg" => "bbb");

        $oUtils = oxNew('oxutils');
        $oUtils->setLangCache($sCacheName, $aLangCache);

        $this->assertEquals($aLangCache, $oUtils->getLangCache($sCacheName));
    }

    /**
     * Seo mode checker
     */
    public function testSeoIsActive()
    {
        // as now SEO is on by default
        $oUtils = oxNew('oxutils');

        $oConfig = Registry::getConfig();
        $oConfig->setConfigParam('aSeoModes', array('testshop' => array(2 => false, 3 => true)));

        $this->assertTrue($oUtils->seoIsActive());

        // cache test
        $this->assertTrue($oUtils->seoIsActive(false, 'testshop', 2));
        $this->assertFalse($oUtils->seoIsActive(true, 'testshop', 2));

        // config test
        $this->assertTrue($oUtils->seoIsActive(true, 'testshop', 3));
    }

    public function testGetArrFldName()
    {
        $sTestString = ".S.o.me.. . Na.me.";
        $sShouldBeResult = "__S__o__me____ __ Na__me__";

        $this->assertEquals($sShouldBeResult, oxRegistry::getUtils()->getArrFldName($sTestString));
    }

    public function optionsAndValuesProvider()
    {
        return array(
            array(true, true, 1),
            array(true, false, 1.2),
            array(false, true, 1.2),
            array(false, false, 1),
        );
    }

    /**
     * Tests how selection lists are outputted with show as net price option, and enter as net price option.
     * Tests all 4 combination of both options.
     *
     * @dataProvider optionsAndValuesProvider
     */
    public function testValueCalculationBasedOnOptions($blEnterNetPrice, $blShowNetPrice, $iVatModifier)
    {
        $myConfig = $this->getConfig();
        $oCurrency = $myConfig->getActShopCurrencyObject();

        $this->getConfig()->setConfigParam('bl_perfLoadSelectLists', true);
        $this->getConfig()->setConfigParam('bl_perfUseSelectlistPrice', true);

        $this->getConfig()->setConfigParam('blEnterNetPrice', $blEnterNetPrice);
        $this->getConfig()->setConfigParam('blShowNetPrice', $blShowNetPrice);

        $sTestString = "one!P!99.5%__oneValue@@two!P!12,41__twoValue@@three!P!-5,99__threeValue@@Lagerort__Lager 1@@";
        $aResult = oxRegistry::getUtils()->assignValuesFromText($sTestString, 20);

        $aShouldBe = array();
        $oObject = new stdClass();
        $oObject->price = '99.5';
        $oObject->priceUnit = '%';
        $oObject->fprice = '99.5%';
        $oObject->name = 'one +99.5%';
        $oObject->value = 'oneValue';
        $aShouldBe[] = $oObject;

        $dPrice = str_replace('.', ',', $this->alterPrice(12.41, $iVatModifier, $blShowNetPrice, $blEnterNetPrice));

        $oObject = new stdClass();
        $oObject->price = '12.41';
        $oObject->fprice = '12,41';
        $oObject->priceUnit = 'abs';
        $oObject->name = "two +$dPrice " . $oCurrency->sign;
        $oObject->value = 'twoValue';
        $aShouldBe[] = $oObject;

        $dPrice = str_replace('.', ',', $this->alterPrice(5.99, $iVatModifier, $blShowNetPrice, $blEnterNetPrice));

        $oObject = new stdClass();
        $oObject->price = '-5.99';
        $oObject->fprice = '-5,99';
        $oObject->priceUnit = 'abs';
        $oObject->name = "three -$dPrice " . $oCurrency->sign;
        $oObject->value = 'threeValue';
        $aShouldBe[] = $oObject;

        $oObject = new stdClass();
        $oObject->name = 'Lagerort';
        $oObject->value = 'Lager 1';
        $aShouldBe[] = $oObject;

        $this->assertEquals($aShouldBe, $aResult);
    }

    /**
     * Helper function to alter prices for checking correct vat prices
     *
     *
     */
    protected function alterPrice($dPrice, $iVatModifier, $blShowNetPrice, $blEnterNetPrice)
    {
        if ($blEnterNetPrice && !$blShowNetPrice) {
            $dPrice *= $iVatModifier;
        } else {
            $dPrice /= $iVatModifier;
        }

        return round($dPrice, 2);
    }

    /**
     * Check of full version processor
     */
    public function testAssignValuesFromTextFull()
    {
        $myConfig = $this->getConfig();
        $oCurrency = $myConfig->getActShopCurrencyObject();

        $this->getConfig()->setConfigParam('bl_perfLoadSelectLists', true);
        $this->getConfig()->setConfigParam('bl_perfUseSelectlistPrice', true);

        $sTestString = "one!P!99.5%__oneValue@@two!P!12,41__twoValue@@three!P!-5,99__threeValue@@Lagerort__Lager 1@@";
        $aResult = oxRegistry::getUtils()->assignValuesFromText($sTestString);

        $aShouldBe = array();
        $oObject = new stdClass();
        $oObject->price = '99.5';
        $oObject->priceUnit = '%';
        $oObject->fprice = '99.5%';
        $oObject->name = 'one +99.5%';
        $oObject->value = 'oneValue';
        $aShouldBe[] = $oObject;

        $oObject = new stdClass();
        $oObject->price = '12.41';
        $oObject->fprice = '12,41';
        $oObject->priceUnit = 'abs';
        $oObject->name = 'two +12,41 ' . $oCurrency->sign;
        $oObject->value = 'twoValue';
        $aShouldBe[] = $oObject;

        $oObject = new stdClass();
        $oObject->price = '-5.99';
        $oObject->fprice = '-5,99';
        $oObject->priceUnit = 'abs';
        $oObject->name = 'three -5,99 ' . $oCurrency->sign;
        $oObject->value = 'threeValue';
        $aShouldBe[] = $oObject;

        $oObject = new stdClass();
        $oObject->name = 'Lagerort';
        $oObject->value = 'Lager 1';
        $aShouldBe[] = $oObject;

        $this->assertEquals($aShouldBe, $aResult);
    }

    /**
     * Check of full version processor
     */
    public function testAssignValuesFromTextFullIfPriceIsZero()
    {
        $myConfig = $this->getConfig();

        $myConfig->setConfigParam('bl_perfLoadSelectLists', true);
        $myConfig->setConfigParam('bl_perfUseSelectlistPrice', true);

        $sTestString = "one__oneValue@@two!P!0.00__twoValue@@";
        $aResult = oxRegistry::getUtils()->assignValuesFromText($sTestString);

        $aShouldBe = array();
        $oObject = new stdClass();
        $oObject->name = 'one';
        $oObject->value = 'oneValue';
        $aShouldBe[] = $oObject;

        $oObject = new stdClass();
        $oObject->price = '0.00';
        $oObject->fprice = '0,00';
        $oObject->priceUnit = 'abs';
        $oObject->name = 'two';
        $oObject->value = 'twoValue';
        $aShouldBe[] = $oObject;

        $this->assertEquals($aShouldBe, $aResult);
    }

    /**
     * Check of full version processor (If NetPrice Is Entered)
     *  FS#2616
     */
    public function testAssignValuesFromTextFullWithVat()
    {
        $myConfig = $this->getConfig();
        $oCurrency = $myConfig->getActShopCurrencyObject();

        $this->getConfig()->setConfigParam('bl_perfLoadSelectLists', true);
        $this->getConfig()->setConfigParam('bl_perfUseSelectlistPrice', true);
        $this->getConfig()->setConfigParam('blEnterNetPrice', true);

        $sTestString = "one!P!99.5%__oneValue@@two!P!12,41__twoValue@@";
        $aResult = oxRegistry::getUtils()->assignValuesFromText($sTestString, 19);

        $aShouldBe = array();
        $oObject = new stdClass();
        $oObject->price = '99.5';
        $oObject->priceUnit = '%';
        $oObject->fprice = '99.5%';
        $oObject->name = 'one +99.5%';
        $oObject->value = 'oneValue';
        $aShouldBe[] = $oObject;

        $oObject = new stdClass();
        $oObject->price = '12.41';
        $oObject->fprice = '12,41';
        $oObject->priceUnit = 'abs';
        $oObject->name = 'two +14,77 ' . $oCurrency->sign;
        $oObject->value = 'twoValue';
        $aShouldBe[] = $oObject;

        $this->assertEquals($aShouldBe, $aResult);
    }

    /**
     * Check of simplified version processor
     */
    public function testAssignValuesFromTextLite()
    {
        $myConfig = $this->getConfig();

        $myConfig->setConfigParam('bl_perfLoadSelectLists', false);
        $myConfig->setConfigParam('bl_perfUseSelectlistPrice', false);

        $sTestString = "one!P!99.5%__oneValue@@two!P!12,41__twoValue@@three!P!-5,99__threeValue@@Lagerort__Lager 1@@";
        $aResult = oxRegistry::getUtils()->assignValuesFromText($sTestString);

        $aShouldBe = array();
        $oObject = new stdClass();
        $oObject->name = 'one';
        $oObject->value = 'oneValue';
        $aShouldBe[] = $oObject;

        $oObject = new stdClass();
        $oObject->name = 'two';
        $oObject->value = 'twoValue';
        $aShouldBe[] = $oObject;

        $oObject = new stdClass();
        $oObject->name = 'three';
        $oObject->value = 'threeValue';
        $aShouldBe[] = $oObject;

        $oObject = new stdClass();
        $oObject->name = 'Lagerort';
        $oObject->value = 'Lager 1';
        $aShouldBe[] = $oObject;

        $this->assertEquals($aShouldBe, $aResult);
    }

    public function testAssignValuesToText()
    {
        $aTestArray = array('one' => 11, 'two' => 22, 'three' => 33, 'fourfour' => 44.44);
        $sResult = oxRegistry::getUtils()->assignValuesToText($aTestArray);
        $sShouldBeResult = "one__11@@two__22@@three__33@@fourfour__44.44@@";
        $sShouldNotBeResult = "on__11@@two__22@@three__33@@fourfour__44.44@@";
        $this->assertEquals($sShouldBeResult, $sResult);
        $this->assertNotEquals($sShouldNotBeResult, $sResult);
    }

    public function testCurrency2Float()
    {
        $oActCur = $this->getConfig()->getActShopCurrencyObject();
        $fFloat = oxRegistry::getUtils()->currency2Float("10.322,32", $oActCur);
        $this->assertEquals($fFloat, 10322.32);
        $fFloat = oxRegistry::getUtils()->currency2Float("10,322.32", $oActCur);
        $this->assertEquals($fFloat, (float) "10.322.32");
        $fFloat = oxRegistry::getUtils()->currency2Float("10 322,32", $oActCur);
        $this->assertEquals($fFloat, (float) "10322.32");
        $fFloat = oxRegistry::getUtils()->currency2Float("10 322.32", $oActCur);
        $this->assertEquals($fFloat, (float) "10322.32");
    }

    /**
     * Testing if shop var saver writes num value with valid string to config correctly
     */
    public function testString2Float()
    {
        $oUtils = oxRegistry::getUtils();
        $fFloat = $oUtils->string2Float("10.322,32");
        $this->assertEquals($fFloat, 10322.32);
        $fFloat = $oUtils->string2Float("10,322.32");
        $this->assertEquals($fFloat, 10322.32);
        $fFloat = $oUtils->string2Float("10322,32");
        $this->assertEquals($fFloat, 10322.32);
        $fFloat = $oUtils->string2Float("10322.32");
        $this->assertEquals($fFloat, 10322.32);
        $fFloat = $oUtils->string2Float("10.32225");
        $this->assertEquals($fFloat, 10.32225);
        $fFloat = $oUtils->string2Float("10 000.32225");
        $this->assertEquals($fFloat, 10000.32225);
        $fFloat = $oUtils->string2Float("10 000.00");
        $this->assertEquals($fFloat, 10000);
    }

    public function testIsSearchEngineAdminAndDebugOff()
    {
        // cleaning ..
        $myConfig = $this->getConfig();

        $myConfig->setConfigParam('iDebug', 1);
        $myConfig->setConfigParam('aRobots', array('googlebot', 'xxx'));

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('isAdmin'));
        $oUtils->expects($this->any())->method('isAdmin')->will($this->returnValue(true));

        $this->assertFalse($oUtils->isSearchEngine('googlebot'));
        $this->assertFalse($oUtils->isSearchEngine('xxx'));
    }

    public function testLoadAdminProfile()
    {
        $aProfiles = oxRegistry::getUtils()->loadAdminProfile(array('640x480', '14'));
        $this->assertContains('640x480', $aProfiles[0]);

        $aProfiles = oxRegistry::getUtils()->loadAdminProfile('v');
        $this->assertNull($aProfiles);

        $aProfiles = oxRegistry::getUtils()->loadAdminProfile("teststring");
        $this->assertNull($aProfiles);
    }

    public function testFRound()
    {
        $myConfig = $this->getConfig();

        $this->assertEquals('9.84', oxRegistry::getUtils()->fRound('9.844'));
        $this->assertEquals('9.85', oxRegistry::getUtils()->fRound('9.845'));
        $this->assertEquals('9.85', oxRegistry::getUtils()->fRound('9.849'));
        $this->assertEquals('0', oxRegistry::getUtils()->fRound('blafoo'));
        $this->assertEquals('9', oxRegistry::getUtils()->fRound('9,849'));

        //negative
        $this->assertEquals('-9.84', oxRegistry::getUtils()->fRound('-9.844'));
        $this->assertEquals('-9.85', oxRegistry::getUtils()->fRound('-9.845'));
        $this->assertEquals('-9.85', oxRegistry::getUtils()->fRound('-9.849'));
        $this->assertEquals('-9', oxRegistry::getUtils()->fRound('-9,849'));


        $aCur = $myConfig->getCurrencyArray();
        $oCur = $aCur[1];
        $this->assertEquals('9.84', oxRegistry::getUtils()->fRound('9.844', $oCur));
        $this->assertEquals('9.85', oxRegistry::getUtils()->fRound('9.845', $oCur));
        $this->assertEquals('9.85', oxRegistry::getUtils()->fRound('9.849', $oCur));
        $this->assertEquals('0', oxRegistry::getUtils()->fRound('blafoo', $oCur));
        $this->assertEquals('9', oxRegistry::getUtils()->fRound('9,849', $oCur));

        $this->assertEquals('-9.84', oxRegistry::getUtils()->fRound('-9.844', $oCur));
        $this->assertEquals('-9.85', oxRegistry::getUtils()->fRound('-9.845', $oCur));
        $this->assertEquals('-9.85', oxRegistry::getUtils()->fRound('-9.849', $oCur));
        $this->assertEquals('-9', oxRegistry::getUtils()->fRound('-9,849', $oCur));

        $this->assertEquals('1522.61', oxRegistry::getUtils()->fRound('1522.605', $oCur));
    }

    public function testToFromStaticCache()
    {
        $oUtils = oxNew('oxutils');

        $sName = "SomeName";
        $mContent = "SomeContent";

        $oUtils->toStaticCache($sName, $mContent);
        $this->assertEquals($mContent, $oUtils->fromStaticCache($sName));

        $sName = "SomeOtherName";
        $mContent = "SomeOtherContent";
        $sKey = "SomeOtherKey";

        $oUtils->toStaticCache($sName, $mContent, $sKey);
        $aOut = $oUtils->fromStaticCache($sName);
        $this->assertEquals($mContent, $aOut[$sKey]);

        // testing non existing
        $this->assertNull($oUtils->fromStaticCache(time()));
    }

    public function testCleanStaticCacheSpecific()
    {
        $oUtils = oxNew('oxutils');

        $sName1 = "SomeName";
        $mContent1 = "SomeContent";

        $sName2 = "SomeName2";
        $mContent2 = "SomeContent2";

        $oUtils->toStaticCache($sName1, $mContent1);
        $oUtils->toStaticCache($sName2, $mContent2);
        $oUtils->cleanStaticCache($sName2);

        $this->assertEquals($mContent1, $oUtils->fromStaticCache($sName1));
        $this->assertEquals(null, $oUtils->fromStaticCache($mContent1));
    }

    public function testCleanStaticCacheFullClean()
    {
        $oUtils = oxNew('oxutils');

        $sName1 = "SomeName";
        $mContent1 = "SomeContent";

        $sName2 = "SomeName2";
        $mContent2 = "SomeContent2";

        $oUtils->toStaticCache($sName1, $mContent1);
        $oUtils->toStaticCache($sName2, $mContent2);
        $oUtils->cleanStaticCache();

        $this->assertEquals(null, $oUtils->fromStaticCache($sName1));
        $this->assertEquals(null, $oUtils->fromStaticCache($sName2));
    }

    public function testToFileCacheFileCache()
    {
        $sName = "testFileCache";
        $sInput = "test_test_test";

        $oUtils = oxNew('oxutils');
        $oUtils->toFileCache($sName, $sInput);
        $this->assertEquals($sInput, $oUtils->fromFileCache($sName));
    }

    public function testToFileCacheFileCacheDoubleWrite1()
    {
        $sName1 = "testFileCache";
        $sName2 = "testFileCache2";
        $sInput1 = "test_test_test";
        $sInput2 = "test_test";

        $oUtils = oxNew('oxutils');
        $oUtils->toFileCache($sName1, $sInput1);
        $oUtils->toFileCache($sName2, $sInput2);
        $this->assertEquals($sInput1, $oUtils->fromFileCache($sName1));
        $this->assertEquals($sInput2, $oUtils->fromFileCache($sName2));
    }

    public function testToFileCacheFileCacheDoubleWrite2()
    {
        $sName1 = "testFileCache";
        $sName2 = "testFileCache2";
        $sInput1 = "test_test_test";
        $sInput2 = "test_test";

        $oUtils = oxNew('oxutils');
        $oUtils->toFileCache($sName1, $sInput1);
        $this->assertEquals($sInput1, $oUtils->fromFileCache($sName1));
        $oUtils->toFileCache($sName2, $sInput2);
        $this->assertEquals($sInput2, $oUtils->fromFileCache($sName2));
    }

    public function testToFileCacheFileCacheDoubleWrite3()
    {
        $sName1 = "testFileCache1";
        $sName2 = "testFileCache2";
        $sInput1 = "test_test_test";
        $sInput2 = "test_test";

        $oUtils = $this->getProxyClass('oxutils');
        $oUtils->toFileCache($sName1, $sInput1);
        $oUtils->toFileCache($sName2, $sInput2);
        $oUtils->commitFileCache();
        $this->assertEquals($sInput1, $oUtils->fromFileCache($sName1));
        $this->assertEquals($sInput2, $oUtils->fromFileCache($sName2));
    }


    public function testOxResetFileCache()
    {
        $myConfig = $this->getConfig();
        $sName = "testFileCache";
        $sInput = "test_test_test";

        //getting cached files prefix
        $myUtilsTest = $this->getProxyClass("oxUtils");
        $sFilePath = $myUtilsTest->getCacheFilePath("test");
        $sCacheFilePrefix = preg_replace("/.*\/(ox[^_]*)_.*/", "$1", $sFilePath);

        $oUtils = oxRegistry::getUtils();
        for ($iMax = 0; $iMax < 10; $iMax++) {
            $oUtils->toFileCache($sName . "_" . $iMax, $sInput . "_" . $iMax);
        }
        $oUtils->commitFileCache();

        //checking if test files were written to temp dir
        $sFilePath = $myConfig->getConfigParam('sCompileDir') . "/{$sCacheFilePrefix}_testFileCache*.txt";
        $aPaths = glob($sFilePath);
        $this->assertEquals(10, count($aPaths), "Error writing test files to cache dir");

        //actual test
        $this->assertNull($oUtils->oxResetFileCache());

        $sFilePath = $myConfig->getConfigParam('sCompileDir') . "/{$sCacheFilePrefix}_testFileCache*.txt";
        $aPaths = glob($sFilePath);
        $this->assertTrue($aPaths == null);
    }

    public function testOxResetFileCacheSkipsTablesFieldNames()
    {
        $myConfig = $this->getConfig();
        $sName = "testFileCache";
        $sInput = "test_test_test";

        //getting cached files prefix
        $myUtilsTest = $this->getProxyClass("oxUtils");
        $sFilePath = $myUtilsTest->getCacheFilePath("test");
        $sCacheFilePrefix = preg_replace("/.*\/(ox[^_]*)_.*/", "$1", $sFilePath);

        //this file must be skipped
        $oUtils = oxRegistry::getUtils();
        $oUtils->toFileCache("fieldnames_testTest", "testCacheValue");
        $oUtils->commitFileCache();

        //checking if test file were written to temp dir
        $sFilePath = $myConfig->getConfigParam('sCompileDir') . "/{$sCacheFilePrefix}_fieldnames_testTest.txt";
        clearstatcache();
        $this->assertTrue(file_exists($sFilePath), "Error writing test files to cache dir");

        for ($iMax = 0; $iMax < 10; $iMax++) {
            $oUtils->toFileCache($sName . "_" . $iMax, $sInput . "_" . $iMax);
        }
        $oUtils->commitFileCache();

        //checking if test files were written to temp dir
        $sFilePath = $myConfig->getConfigParam('sCompileDir') . "/{$sCacheFilePrefix}_testFileCache*.txt";
        $aPaths = glob($sFilePath);
        $this->assertEquals(10, count($aPaths), "Error writing test files to cache dir: " . count($aPaths));

        //actual test
        $this->assertNull($oUtils->oxResetFileCache());

        $sFilePath = $myConfig->getConfigParam('sCompileDir') . "/{$sCacheFilePrefix}_fieldnames_testTest.txt";
        $aPaths = glob($sFilePath);

        @unlink($aPaths[0]); //deleting test cache file
        $this->assertEquals(1, count($aPaths));
    }

    public function testResetLanguageCache()
    {
        $oUtils = oxRegistry::getUtils();

        $aFiles = array('langcache_1_a', 'langcache_1_b', 'langcache_1_c');
        foreach ($aFiles as $sFile) {
            $oUtils->setLangCache($sFile, array($sFile));
        }

        foreach ($aFiles as $sFile) {
            $this->assertEquals(array($sFile), $oUtils->getLangCache($sFile));
        }

        $this->assertNull($oUtils->resetLanguageCache());

        foreach ($aFiles as $sFile) {
            $this->assertNull($oUtils->getLangCache($sFile));
        }
    }

    public function testCheckAccessRights()
    {
        $mySession = oxRegistry::getSession();
        $backUpAuth = $mySession->getVariable("auth");

        $mySession->setVariable("auth", "oxdefaultadmin");
        $this->assertEquals(true, oxRegistry::getUtils()->checkAccessRights());

        //  self::$test_sql_used = null;
        modDB::getInstance()->addClassFunction('getOne', function ($sql) {
            return 1;
        });

        $mySession->setVariable("auth", "oxdefaultadmin");
        $this->assertEquals(true, oxRegistry::getUtils()->checkAccessRights());
        $mySession->setVariable("auth", "blafooUser");


        //self::$test_sql_used = null;
        modDB::getInstance()->addClassFunction('getOne', function ($sql) {
            return 0;
        });

        $this->assertEquals(false, oxRegistry::getUtils()->checkAccessRights());

        $mySession->setVariable("auth", $backUpAuth);
        modDB::getInstance()->cleanup();
    }

    public function testCheckAccessRightsChecksSubshopAdminShop()
    {
        $session = Registry::getSession();
        $backUpAuth = $session->getVariable("auth");

        $exception = null;

        try {
            $utils = $this->getMock('OxidEsales\EshopCommunity\Core\Utils', array('fetchRightsForUser', 'fetchShopAdminById'));
            $utils->expects($this->any())->method('fetchRightsForUser')->will($this->returnValue(1));
            $utils->expects($this->any())->method('fetchShopAdminById')->will($this->returnValue(1));


            $session->setVariable("auth", "blafooUser");
            $this->assertEquals(true, $utils->checkAccessRights());
            $this->setRequestParameter('fnc', 'chshp');
            $this->assertEquals(false, $utils->checkAccessRights());
            $this->setRequestParameter('fnc', null);
            $this->assertEquals(true, $utils->checkAccessRights());

            $this->setRequestParameter('actshop', 1);
            $this->assertEquals(true, $utils->checkAccessRights());
            $this->setRequestParameter('actshop', 2);
            $this->assertEquals(false, $utils->checkAccessRights());
            $this->setRequestParameter('actshop', null);
            $this->assertEquals(true, $utils->checkAccessRights());

            $this->setRequestParameter('shp', 1);
            $this->assertEquals(true, $utils->checkAccessRights());
            $this->setRequestParameter('shp', 2);
            $this->assertEquals(false, $utils->checkAccessRights());
            $this->setRequestParameter('shp', null);
            $this->assertEquals(true, $utils->checkAccessRights());

            $this->setRequestParameter('currentadminshop', 1);
            $this->assertEquals(true, $utils->checkAccessRights());
            $this->setRequestParameter('currentadminshop', 2);
            $this->assertEquals(false, $utils->checkAccessRights());
            $this->setRequestParameter('currentadminshop', null);
            $this->assertEquals(true, $utils->checkAccessRights());
        } catch (Exception  $exception) {
        }

        $session->setVariable("auth", $backUpAuth);

        if ($exception) {
            throw $exception;
        }
    }

    public function testIsValidAlpha()
    {
        $this->assertEquals(true, oxRegistry::getUtils()->isValidAlpha('oxid'));
        $this->assertEquals(true, oxRegistry::getUtils()->isValidAlpha('oxid1'));
        $this->assertEquals(false, oxRegistry::getUtils()->isValidAlpha('oxid.'));
        $this->assertEquals(false, oxRegistry::getUtils()->isValidAlpha('oxid{'));
        $this->assertEquals(true, oxRegistry::getUtils()->isValidAlpha('oxi_d'));
        $this->assertEquals(false, oxRegistry::getUtils()->isValidAlpha('ox\\id'));
    }

    public function testAddUrlParameters()
    {
        $oUtils = oxNew('oxUtils');

        $sURL = 'http://www.url.com';
        $aParams = array('string' => 'someString', 'bool1' => false, 'bool2' => true, 'int' => 1234, 'float' => 123.45, 'negfloat' => -123.45);

        $sReturnURL = "http://www.url.com?string=someString&bool1=&bool2=1&int=1234&float=123.45&negfloat=-123.45";
        $this->assertEquals($sReturnURL, $oUtils->addUrlParameters($sURL, $aParams));

        $sURL = 'http://www.url.com/index.php?cl=aaa';
        $sReturnURL = "http://www.url.com/index.php?cl=aaa&string=someString&bool1=&bool2=1&int=1234&float=123.45&negfloat=-123.45";
        $this->assertEquals($sReturnURL, $oUtils->addUrlParameters($sURL, $aParams));
    }

    public function testOxMimeContentType()
    {
        $oUtils = oxNew('oxUtils');
        $sFile = 'asdnasd/asdasd.asd.ad.ad.asd.gif';
        $this->assertEquals('image/gif', $oUtils->oxMimeContentType($sFile));

        $sFile = 'asdnasd/asdasd.asd.ad.ad.asd.jpeg';
        $this->assertEquals('image/jpeg', $oUtils->oxMimeContentType($sFile));

        $sFile = 'asdnasd/asdasd.asd.ad.ad.asd.jpg';
        $this->assertEquals('image/jpeg', $oUtils->oxMimeContentType($sFile));

        $sFile = 'asdnasd/asdasd.asd.ad.ad.asd.png';
        $this->assertEquals('image/png', $oUtils->oxMimeContentType($sFile));

        $sFile = 'asdnasd/asdasd.asd.ad.ad.asdjpeg';
        $this->assertEquals(false, $oUtils->oxMimeContentType($sFile));
        $this->assertEquals(false, $oUtils->oxMimeContentType(''));
    }

    public function testRedirect()
    {
        $session = $this->getMock(\OxidEsales\Eshop\Core\Session::class, array('freeze'));
        $session->expects($this->once())->method('freeze');
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Session::class, $session);

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('simpleRedirect', 'showMessageAndExit'));
        $oUtils->expects($this->once())->method('simpleRedirect')->with($this->equalTo('url?redirected=1'));
        $oUtils->redirect('url');
    }

    public function providerRedirectCodes()
    {
        return array(
            array(301, 'HTTP/1.1 301 Moved Permanently'),
            array(302, 'HTTP/1.1 302 Found'),
            array(500, 'HTTP/1.1 500 Internal Server Error'),
            array(423958, 'HTTP/1.1 302 Found'),
        );
    }

    /**
     * @param int    $iCode   header code
     * @param string $sHeader formed expected header string
     *
     * @dataProvider providerRedirectCodes
     */
    public function testRedirectCodes($iCode, $sHeader)
    {
        $session = $this->getMock(\OxidEsales\Eshop\Core\Session::class, array('freeze'));
        $session->expects($this->any())->method('freeze');
        \OxidEsales\Eshop\Core\Registry::set(\OxidEsales\Eshop\Core\Session::class, $session);

        // test also any other to redirect only temporary
        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('simpleRedirect', 'showMessageAndExit'));
        $oUtils->expects($this->once())->method('simpleRedirect')->with($this->equalTo('url'), $this->equalTo($sHeader));
        $oUtils->redirect('url', false, $iCode);
    }

    public function testReRedirect()
    {
        $this->setRequestParameter('redirected', '1');

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('simpleRedirect', 'addUrlParameters'));
        $oUtils->expects($this->never())->method('simpleRedirect');
        $oUtils->expects($this->never())->method('addUrlParameters');
        $oUtils->redirect('url');
    }

    public function testRedirectWithEncodedEntities()
    {
        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('simpleRedirect', 'showMessageAndExit'));
        $oUtils->expects($this->once())->method('simpleRedirect')->with($this->equalTo('url?param1=1&param2=2&param3=3&redirected=1'));
        $oUtils->redirect('url?param1=1&param2=2&amp;param3=3');
    }

    public function testFromFileCacheEmpty()
    {
        $oUtils = oxNew('oxutils');
        $sCacheHit = $oUtils->fromFileCache("notexistantkey");
        $this->assertFalse($sCacheHit === false);
        $this->assertNull($sCacheHit);
    }

    public function testCheckUrlEndingSlash()
    {
        $oUtils = oxNew('oxutils');
        $this->assertEquals("http://www.site.de/", $oUtils->checkUrlEndingSlash("http://www.site.de/"));
        $this->assertEquals("http://www.site.de/", $oUtils->checkUrlEndingSlash("http://www.site.de"));
    }

    public function testCacheRaceConditions0Size()
    {
        $oUtils = oxNew('oxutils');
        $sFileName = $oUtils->getCacheFilePath('testCache1');
        @unlink($sFileName);
        $oUtils->toFileCache('testCache1', 'teststs');
        $oUtils->commitFileCache();
        $this->assertEquals(serialize(array('content' => 'teststs')), file_get_contents($sFileName));
        unlink($sFileName);
    }

    public function testCacheRaceConditionsNon0Size()
    {
        $oUtils = oxNew('oxutils');
        $sFileName = $oUtils->getCacheFilePath('testCache2');
        @unlink($sFileName);
        $oUtils->toFileCache('testCache2', 'teststs');
        $oUtils->commitFileCache();
        $sFileContents = file_get_contents($sFileName);
        $this->assertEquals(serialize(array('content' => 'teststs')), $sFileContents);
        unlink($sFileName);
    }

    public function testCacheRaceConditionsIgnoredBySisterProcess()
    {
        $oUtils1 = oxNew('oxutils');
        $oUtils2 = oxNew('oxutils');
        $sFileName = $oUtils1->getCacheFilePath('testCache3');
        @unlink($sFileName);
        $oUtils1->toFileCache('testCache3', 'instance1111');
        $oUtils2->toFileCache('testCache3', 'instance2222');
        $oUtils1->commitFileCache();
        $oUtils2->commitFileCache();
        $sFileContents = file_get_contents($sFileName);
        $this->assertEquals(serialize(array('content' => 'instance1111')), $sFileContents);
        unlink($sFileName);
    }

    public function testCachingLockRelease()
    {
        clearstatcache();
        $oUtils1 = oxNew('oxutils');
        $sFileName = $oUtils1->getCacheFilePath('testCache3');
        @unlink($sFileName);
        $this->assertFalse(file_exists($sFileName));

        $oUtils1->toFileCache('testCache3', 'instance1111');
        clearstatcache();
        $this->assertTrue(file_exists($sFileName));
        $this->assertEquals(0, filesize($sFileName));

        $oUtils1->commitFileCache();
        clearstatcache();
        $this->assertEquals(serialize(array('content' => 'instance1111')), file_get_contents($sFileName));
        $this->assertNotEquals(0, filesize($sFileName));

        $oUtils2 = oxNew('oxutils');
        $oUtils2->toFileCache('testCache3', 'instance2222');
        clearstatcache();
        $this->assertTrue(file_exists($sFileName));
        $this->assertEquals(0, filesize($sFileName));

        $oUtils2->commitFileCache();
        clearstatcache();
        $this->assertEquals(serialize(array('content' => 'instance2222')), file_get_contents($sFileName));
        $this->assertNotEquals(0, filesize($sFileName));

        unlink($sFileName);
    }

    /**
     *
     */
    public function testCanPreview()
    {
        $this->setRequestParameter("preview", null);
        $oUtils = oxNew('oxUtils');
        $this->assertNull($oUtils->canPreview());

        $this->setRequestParameter("preview", "132");
        oxTestModules::addFunction('oxUtilsServer', 'getOxCookie', '{ return "123"; }');
        $this->assertFalse($oUtils->canPreview());

        $oUser = oxNew('oxUser');
        $oUser->load("oxdefaultadmin");

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array("getUser"));
        $oUtils->expects($this->any())->method("getUser")->will($this->returnValue($oUser));

        $this->setRequestParameter("preview", $oUtils->getPreviewId());
        oxTestModules::addFunction('oxUtilsServer', 'getOxCookie', '{ return "123"; }');

        $this->assertTrue($oUtils->canPreview());
    }

    /**
     * oxUtils::getPreviewId() test case
     */
    public function testGetPreviewId()
    {
        $sAdminSid = \OxidEsales\Eshop\Core\Registry::getUtilsServer()->getOxCookie('admin_sid');
        $sCompare = md5($sAdminSid . "testID" . "testPass" . "tesrRights");

        $oUser = $this->getMock(\OxidEsales\Eshop\Application\Model\User::class, array("getId"));
        $oUser->expects($this->once())->method("getId")->will($this->returnValue("testID"));
        $oUser->oxuser__oxpassword = new oxField("testPass");
        $oUser->oxuser__oxrights = new oxField("tesrRights");

        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array("getUser"));
        $oUtils->expects($this->once())->method("getUser")->will($this->returnValue($oUser));

        $this->assertEquals($sCompare, $oUtils->getPreviewId());
    }

    /**
     * Test case for oxUtils::handlePageNotFoundError.
     */
    public function testHandlePageNotFoundErrorWithoutUrl()
    {
        oxTestModules::addFunction('oxutils', 'showMessageAndExit', '{$this->showMessageAndExitCall[] = $aA; }');
        oxTestModules::addFunction('oxutils', 'setHeader', '{$this->setHeaderCall[] = $aA;}');
        oxTestModules::addFunction('oxUtilsView', 'getTemplateOutput', '{$this->getTemplateOutputCall[] = $aA; return "msg_".count($this->getTemplateOutputCall);}');

        oxRegistry::getUtils()->handlePageNotFoundError();
        $this->assertEquals(1, count(\OxidEsales\Eshop\Core\Registry::getUtilsView()->getTemplateOutputCall));
        $this->assertEquals(1, count(oxRegistry::getUtils()->showMessageAndExitCall));
        $this->assertEquals('msg_1', oxRegistry::getUtils()->showMessageAndExitCall[0][0]);
        $expectedHeaders = array(
            array('HTTP/1.0 404 Not Found'),
            array('Content-Type: text/html; charset=UTF-8')
        );
        $this->assertEquals($expectedHeaders, oxRegistry::getUtils()->setHeaderCall);
    }

    /**
     * Test case for oxUtils::handlePageNotFoundError.
     */
    public function testHandlePageNotFoundErrorWithUrl()
    {
        oxTestModules::addFunction('oxutils', 'showMessageAndExit', '{$this->showMessageAndExitCall[] = $aA; }');
        oxTestModules::addFunction('oxutils', 'setHeader', '{$this->setHeaderCall[] = $aA;}');
        oxTestModules::addFunction('oxUtilsView', 'getTemplateOutput', '{$this->getTemplateOutputCall[] = $aA; return "msg_".count($this->getTemplateOutputCall);}');

        oxRegistry::getUtils()->handlePageNotFoundError('url aa');
        $this->assertEquals(1, count(\OxidEsales\Eshop\Core\Registry::getUtilsView()->getTemplateOutputCall));
        $this->assertEquals(1, count(oxRegistry::getUtils()->showMessageAndExitCall));
        $this->assertEquals('msg_1', oxRegistry::getUtils()->showMessageAndExitCall[0][0]);
        $expectedHeaders = array(
            array('HTTP/1.0 404 Not Found'),
            array('Content-Type: text/html; charset=UTF-8')
        );
        $this->assertEquals($expectedHeaders, oxRegistry::getUtils()->setHeaderCall);
    }

    /**
     * Test case for oxUtils::handlePageNotFoundError.
     *
     * A rendering exception indicates that the shop itself is not working, so
     * the exception should be passed through and end up in the error log.
     */
    public function testHandlePageNotFoundErrorWithUrlAndRenderException()
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Some rendering exception');

        oxTestModules::addFunction('oxutils', 'showMessageAndExit', '{$this->showMessageAndExitCall[] = $aA; }');
        oxTestModules::addFunction('oxutils', 'setHeader', '{$this->setHeaderCall[] = $aA;}');
        oxTestModules::addFunction('oxUtilsView', 'getTemplateOutput', '{$this->getTemplateOutputCall[] = $aA; return "msg_".count($this->getTemplateOutputCall);}');

        oxTestModules::addFunction(
            'oxUBase',
            'render',
            '{throw new Exception(\'Some rendering exception\');}'
        );

        oxRegistry::getUtils()->handlePageNotFoundError('url aa');
    }

    public function testToPhpFileCache()
    {
        $sTestArray = array("testVal1", "key1" => "testVal2");

        $oUtils = oxRegistry::getUtils();
        $oUtils->toPhpFileCache("testVal", $sTestArray);
        $oUtils->commitFileCache();

        $sFileName = oxRegistry::getUtils()->getCacheFilePath("testVal", false, 'php');

        include($sFileName);

        $this->assertEquals($_aCacheContents['content'], $sTestArray);
        unlink($sFileName);
    }

    /**
     * Test for bug #1737
     *
     */
    public function testToPhpFileCacheException()
    {
        $oSubj = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array("getCacheFilePath"));
        $oSubj->expects($this->any())->method("getCacheFilePath")->will($this->returnValue(false));

        oxTestModules::addModuleObject("oxUtils", $oSubj);

        $sTestArray = array("testVal1", "key1" => "testVal2");
        oxRegistry::getUtils()->toPhpFileCache("testVal2", $sTestArray);
        $aCacheContents = oxRegistry::getUtils()->fromPhpFileCache("testVal2");

        $this->assertNull($aCacheContents);
    }

    public function testFromPhpFileCache()
    {
        $sTestArray = array("testVal1", "key1" => "testVal2");

        $oUtils = oxRegistry::getUtils();
        $oUtils->toPhpFileCache("testVal", $sTestArray);
        $oUtils->commitFileCache();

        $this->assertEquals($oUtils->fromPhpFileCache("testVal"), $sTestArray);
    }

    /**
     * oxUtils::getCacheMeta() & oxUtils::setCacheMeta() test case
     *
     * @return null
     */
    public function testGetCacheMetaSetCacheMeta()
    {
        $oUtils = oxNew('oxUtils');
        $oUtils->setCacheMeta("xxx", "yyy");

        $this->assertFalse($oUtils->getCacheMeta("yyy"));
        $this->assertEquals("yyy", $oUtils->getCacheMeta("xxx"));
    }

    /**
     * oxUtils::_readFile() test case
     *
     * @return null
     */
    public function testReadFile()
    {
        $sFilePath = oxRegistry::getUtils()->getCacheFilePath("testVal", false, 'php');
        if (($hFile = @fopen($sFilePath, "w")) !== false) {
            fwrite($hFile, serialize("test"));
            fclose($hFile);

            $oUtils = oxNew('oxUtils');
            $this->assertEquals("test", $oUtils->readFile($sFilePath));

            return;
        }

        $this->markTestSkipped("Unable to create file {$sFilePath}");
    }

    /**
     * oxUtils::_includeFile() test case
     *
     * @return null
     */
    public function testIncludeFile()
    {
        $sFilePath = oxRegistry::getUtils()->getCacheFilePath("testVal", false, 'php');
        if (($hFile = @fopen($sFilePath, "w")) !== false) {
            fwrite($hFile, '<?php $_aCacheContents = "test123";');
            fclose($hFile);

            $oUtils = oxNew('oxUtils');
            $this->assertEquals("test123", $oUtils->includeFile($sFilePath));

            return;
        }

        $this->markTestSkipped("Unable to create file {$sFilePath}");
    }

    /**
     * oxUtils::_processCache() test case
     *
     * @return null
     */
    public function testProcessCache()
    {
        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array("getCacheMeta"));
        $oUtils
            ->method('getCacheMeta')
            ->willReturnOnConsecutiveCalls(
                false,
                ["serialize" => false]
            );

        $this->assertEquals(serialize(123), $oUtils->processCache(123, 123));
        $this->assertNotEquals(serialize(123), $oUtils->processCache(123, 123));
    }

    /**
     * Tests if cache works when TTL is not exceeded
     */
    public function testGetTtlCachingInTime()
    {
        $this->setTime(10);

        $oUtils = oxNew('oxUtils');
        $oUtils->toFileCache('anykey', 'test', 10);
        $oUtils->commitFileCache();

        $oUtils2 = oxNew('oxUtils');

        $this->setTime(15);
        $this->assertEquals('test', $oUtils2->fromFileCache('anykey'));
    }

    /**
     * Tests if cache works when TTL is exceeded
     */
    public function testGetTtlCachingTooLate()
    {
        $this->setTime(10);
        $oUtils = oxNew('oxUtils');
        $oUtils->toFileCache('otherkey', 'test', 10);
        $oUtils->commitFileCache();

        $oUtils2 = oxNew('oxUtils');

        $this->setTime(145);
        $this->assertEquals(null, $oUtils2->fromFileCache('otherkey'));
    }

    /**
     * Test if we will get correct prefix depending on version
     */
    public function testGetEditionCacheFilePrefix()
    {
        if ($this->getTestConfig()->getShopEdition() !== 'CE') {
            $this->markTestSkipped('This test is for Community edition only');
        }

        $utils = oxNew('oxUtils');
        $expected = '';
        $this->assertSame($expected, $utils->getEditionCacheFilePrefix());
    }

    /**
     * Bug fix 0005811: Selectlist prices are displayed wrong under certain circumstances
     */
    public function testPreparePriceForUserWithChangedBehaviourWhenBruttoMode()
    {
        $this->setConfigParam('blShowNetPrice', false);

        // Mocking not necessary method for testing method to be called. Leaving mock empty would stub all class methods.
        /** @var oxUtils|PHPUnit\Framework\MockObject\MockObject $oUtils */
        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('getArticleUser'));
        $this->assertSame(10, $oUtils->preparePrice(10, 10));
    }

    /**
     * Bug fix 0005811: Selectlist prices are displayed wrong under certain circumstances
     */
    public function testPreparePriceForUserWithChangedBehaviourWhenNettoMode()
    {
        $this->setConfigParam('blShowNetPrice', true);
        // Mocking not necessary method for testing method to be called. Leaving mock empty would stub all class methods.
        /** @var oxUtils|PHPUnit\Framework\MockObject\MockObject $oUtils */
        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('getArticleUser'));
        $this->assertSame(9.09, $oUtils->preparePrice(10, 10));
    }

    /**
     * Bug fix 0005811: Selectlist prices are displayed wrong under certain circumstances
     */
    public function testPreparePriceForUserWithChangedBehaviourWhenNettoModeButUserBruttoMode()
    {
        $this->setConfigParam('blShowNetPrice', true);

        $oUser = $this->getMock(\OxidEsales\Eshop\Application\Model\User::class, array('isPriceViewModeNetto'));
        $oUser->expects($this->any())->method('isPriceViewModeNetto')->will($this->returnValue(false));

        // Mocking not necessary method for testing method to be called. Leaving mock empty would stub all class methods.
        /** @var oxUtils|PHPUnit\Framework\MockObject\MockObject $oUtils */
        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('getArticleUser'));
        $oUtils->expects($this->atLeastOnce())->method('getArticleUser')->will($this->returnValue($oUser));
        $this->assertSame(10, $oUtils->preparePrice(10, 10));
    }

    /**
     * Bug fix 0005811: Selectlist prices are displayed wrong under certain circumstances
     */
    public function testPreparePriceForUserWithChangedBehaviourWhenBruttoModeButUserNettoMode()
    {
        $this->setConfigParam('blShowNetPrice', false);

        $oUser = $this->getMock(\OxidEsales\Eshop\Application\Model\User::class, array('isPriceViewModeNetto'));
        $oUser->expects($this->any())->method('isPriceViewModeNetto')->will($this->returnValue(true));

        // Mocking not necessary method for testing method to be called. Leaving mock empty would stub all class methods.
        /** @var oxUtils|PHPUnit\Framework\MockObject\MockObject $oUtils */
        $oUtils = $this->getMock(\OxidEsales\Eshop\Core\Utils::class, array('getArticleUser'));
        $oUtils->expects($this->atLeastOnce())->method('getArticleUser')->will($this->returnValue($oUser));
        $this->assertSame(9.09, $oUtils->preparePrice(10, 10));
    }
}
