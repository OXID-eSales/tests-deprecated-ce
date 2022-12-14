<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Application\Controller\Admin;

use OxidEsales\Eshop\Core\Config;
use OxidEsales\EshopCommunity\Application\Model\Article;
use OxidEsales\EshopCommunity\Core\Exception\ExceptionToDisplay;
use \oxField;
use \oxDb;
use OxidEsales\EshopCommunity\Core\Registry;
use \oxRegistry;
use \oxTestModules;

/**
 * Tests for Article_Pictures class
 */
class ArticlePicturesTest extends \OxidTestCase
{
    /**
     * Initialize the fixture.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->_oArticle = oxNew('oxArticle');
        $this->_oArticle->setId("_testArtId");
        $this->_oArticle->save();
    }

    /**
     * Tear down the fixture.
     */
    protected function tearDown(): void
    {
        $this->cleanUpTable('oxarticles');

        parent::tearDown();
    }

    /**
     * Article_Pictures::save() test case
     */
    public function testSaveAdditionalTest()
    {
        oxTestModules::addFunction('oxarticle', 'save', '{ return true; }');
        $this->getConfig()->setConfigParam('iPicCount', 0);

        $oView = $this->getMock(\OxidEsales\Eshop\Application\Controller\Admin\ArticlePictures::class, array("resetContentCache"));
        $oView->expects($this->once())->method('resetContentCache');

        $iCnt = 7;
        $this->getConfig()->setConfigParam('iPicCount', $iCnt);

        $oView->save();
    }

    /**
     * Article_Pictures::Render() test case
     */
    public function testRender()
    {
        oxTestModules::addFunction('oxarticle', 'isDerived', '{ return true; }');
        $this->setRequestParameter("oxid", oxDb::getDb()->getOne("select oxid from oxarticles where oxparentid != ''"));

        // testing..
        $oView = oxNew('Article_Pictures');
        $sTplName = $oView->render();

        // testing view data
        $aViewData = $oView->getViewData();
        $this->assertTrue($aViewData["edit"] instanceof Article);
        $this->assertTrue($aViewData["parentarticle"] instanceof Article);

        $this->assertEquals('article_pictures', $sTplName);
    }

    /**
     * Article_Pictures::deletePicture() test case - deleting icon
     */
    public function testDeletePicture_deletingIcon()
    {
        $this->setRequestParameter("oxid", "_testArtId");
        $this->setRequestParameter("masterPicIndex", "ICO");

        $oDb = oxDb::getDb(oxDB::FETCH_MODE_ASSOC);

        $oArtPic = $this->getMock(\OxidEsales\Eshop\Application\Controller\Admin\ArticlePictures::class, array("deleteThumbnail", "deleteMasterPicture"));
        $oArtPic->expects($this->never())->method('deleteThumbnail');
        $oArtPic->expects($this->never())->method('deleteMasterPicture');

        $this->_oArticle->oxarticles__oxicon = new oxField("testIcon.jpg");
        $this->_oArticle->save();

        $oArtPic->deletePicture();

        $this->assertEquals("", $oDb->getOne("select oxicon from oxarticles where oxid='_testArtId' "));
    }

    /**
     * Article_Pictures::deletePicture() test case - deleting thumbnail
     */
    public function testDeletePicture_deletingThumbnail()
    {
        $this->setRequestParameter("oxid", "_testArtId");
        $this->setRequestParameter("masterPicIndex", "TH");
        $oDb = oxDb::getDb(oxDB::FETCH_MODE_ASSOC);

        $oArtPic = $this->getMock(\OxidEsales\Eshop\Application\Controller\Admin\ArticlePictures::class, array("deleteMainIcon", "deleteMasterPicture"));
        $oArtPic->expects($this->never())->method('deleteMainIcon');
        $oArtPic->expects($this->never())->method('deleteMasterPicture');

        $this->_oArticle->oxarticles__oxthumb = new oxField("testThumb.jpg");
        $this->_oArticle->save();

        $oArtPic->deletePicture();

        $this->assertEquals("", $oDb->getOne("select oxthumb from oxarticles where oxid='_testArtId' "));
    }

    /**
     * Article_Pictures::deletePicture() test case - deleting master picture
     */
    public function testDeletePicture_deletingMasterPic()
    {
        $this->setRequestParameter("oxid", "_testArtId");
        $this->setRequestParameter("masterPicIndex", "2");
        $oDb = oxDb::getDb(oxDB::FETCH_MODE_ASSOC);

        $oArtPic = $this->getMock(\OxidEsales\Eshop\Application\Controller\Admin\ArticlePictures::class, array("deleteMainIcon", "deleteThumbnail"));
        $oArtPic->expects($this->never())->method('deleteMainIcon');
        $oArtPic->expects($this->never())->method('deleteThumbnail');

        $this->_oArticle->oxarticles__oxpic2 = new oxField("testPic2.jpg");
        $this->_oArticle->save();

        $oArtPic->deletePicture();

        $this->assertEquals("", $oDb->getOne("select oxpic2 from oxarticles where oxid='_testArtId' "));
    }

    /**
     * Article_Pictures::deletePicture() test case - updating amount of genereated pictures
     */
    public function testDeletePicture_generatedPicsCounterReset()
    {
        $this->setRequestParameter("oxid", "_testArtId");
        $this->setRequestParameter("masterPicIndex", "2");

        $oArtPic = $this->getMock(\OxidEsales\Eshop\Application\Controller\Admin\ArticlePictures::class, array("resetMasterPicture"));
        $oArtPic->expects($this->once())->method('resetMasterPicture');

        $this->_oArticle->save();

        $oArtPic->deletePicture();

        $this->_oArticle->load("_testArtId");
    }

    /**
     * Article_Pictures::deleteMainIcon()
     */
    public function testDeleteMainIcon()
    {
        $oArticle = $this->getMock(\OxidEsales\Eshop\Application\Model\Article::class, array('isDerived'));
        $oArticle->expects($this->atLeastOnce())->method('isDerived')->will($this->returnValue(null));

        $oArticle->oxarticles__oxicon = new oxField("testIcon.jpg");

        $oPicHandler = $this->getMock(\OxidEsales\Eshop\Core\PictureHandler::class, array("deleteMainIcon"));
        $oPicHandler->expects($this->once())->method('deleteMainIcon');

        oxTestModules::addModuleObject("oxPictureHandler", $oPicHandler);

        $oArtPic = $this->getProxyClass("Article_Pictures");
        $oArtPic->deleteMainIcon($oArticle);

        $this->assertEquals("", $oArticle->oxarticles__oxicon->value);
    }

    /**
     * Article_Pictures::deleteThumbnail()
     */
    public function testDeleteThumbnail()
    {
        $oArticle = $this->getMock(\OxidEsales\Eshop\Application\Model\Article::class, array('isDerived'));
        $oArticle->expects($this->atLeastOnce())->method('isDerived')->will($this->returnValue(null));

        $oArticle->oxarticles__oxthumb = new oxField("testThumb.jpg");

        $oPicHandler = $this->getMock(\OxidEsales\Eshop\Core\PictureHandler::class, array("deleteThumbnail"));
        $oPicHandler->expects($this->once())->method('deleteThumbnail');

        oxTestModules::addModuleObject("oxPictureHandler", $oPicHandler);

        $oArtPic = $this->getProxyClass("Article_Pictures");
        $oArtPic->deleteThumbnail($oArticle);

        $this->assertEquals("", $oArticle->oxarticles__oxthumb->value);
    }

    /**
     * Article_Pictures::_resetMasterPicture()
     */
    public function testResetMasterPicture()
    {
        $oArticle = $this->getMock(\OxidEsales\Eshop\Application\Model\Article::class, array('isDerived'));
        $oArticle->expects($this->atLeastOnce())->method('isDerived')->will($this->returnValue(null));

        $oArticle->oxarticles__oxpic2 = new oxField("testPic2.jpg");

        $oPicHandler = $this->getMock(\OxidEsales\Eshop\Core\PictureHandler::class, array("deleteArticleMasterPicture"));
        $oPicHandler->expects($this->once())->method('deleteArticleMasterPicture')->with($this->equalTo($oArticle), $this->equalTo(2), $this->equalTo(false));

        oxTestModules::addModuleObject("oxPictureHandler", $oPicHandler);

        $oArtPic = $this->getProxyClass("Article_Pictures");
        $oArtPic->resetMasterPicture($oArticle, 2);

        $this->assertEquals("testPic2.jpg", $oArticle->oxarticles__oxpic2->value);
    }

    /**
     * Article_Pictures::_resetMasterPicture() - calling cleanup when field
     * index = 1
     */
    public function testResetMasterPicture_makesCleanupOnFields()
    {
        $oArticle = $this->getMock(\OxidEsales\Eshop\Application\Model\Article::class, array('isDerived'));
        $oArticle->expects($this->atLeastOnce())->method('isDerived')->will($this->returnValue(null));

        $oArticle->oxarticles__oxpic1 = new oxField("testPic1.jpg");
        $oArticle->oxarticles__oxpic2 = new oxField("testPic2.jpg");

        $oPicHandler = $this->getMock(\OxidEsales\Eshop\Core\PictureHandler::class, array("deleteArticleMasterPicture"));
        $oPicHandler->expects($this->exactly(2))->method('deleteArticleMasterPicture');

        oxTestModules::addModuleObject("oxPictureHandler", $oPicHandler);

        $oArtPic = $this->getMock(\OxidEsales\Eshop\Application\Controller\Admin\ArticlePictures::class, array("cleanupCustomFields"));
        $oArtPic->expects($this->never())->method('cleanupCustomFields');
        $oArtPic->resetMasterPicture($oArticle, 2);

        $oArtPic = $this->getMock(\OxidEsales\Eshop\Application\Controller\Admin\ArticlePictures::class, array("cleanupCustomFields"));
        $oArtPic->expects($this->once())->method('cleanupCustomFields');
        $oArtPic->resetMasterPicture($oArticle, 1);
    }

    /**
     * Article_Pictures::cleanupCustomFields()
     *
     * @return null
     */
    public function testCleanupCustomFields()
    {
        $this->_oArticle->oxarticles__oxicon = new oxField("nopic.jpg");
        $this->_oArticle->oxarticles__oxthumb = new oxField("nopic.jpg");

        $_FILES['myfile']['name']["M2"] = "value1";
        $_FILES['myfile']['name']["M3"] = "value2";

        $oArtPic = $this->getProxyClass("Article_Pictures");

        $oArtPic->cleanupCustomFields($this->_oArticle);

        $this->assertEquals("", $this->_oArticle->oxarticles__oxicon->value);
        $this->assertEquals("", $this->_oArticle->oxarticles__oxthumb->value);
    }

    /**
     * Article_Pictures::cleanupCustomFields() - when custom fields are not empty
     *
     * @return null
     */
    public function testCleanupCustomFields_fieldsNotEmpty()
    {
        $this->_oArticle->oxarticles__oxicon = new oxField("testIcon.jpg");
        $this->_oArticle->oxarticles__oxthumb = new oxField("testThumb.jpg");

        $_FILES['myfile']['name']["M2"] = "value1";
        $_FILES['myfile']['name']["M3"] = "value2";

        $oArtPic = $this->getProxyClass("Article_Pictures");

        $oArtPic->cleanupCustomFields($this->_oArticle);

        $this->assertEquals("testIcon.jpg", $this->_oArticle->oxarticles__oxicon->value);
        $this->assertEquals("testThumb.jpg", $this->_oArticle->oxarticles__oxthumb->value);
    }

    /**
     * Article_Pictures::save() - in demo shop mode
     *
     * @return null
     */
    public function testSave_demoShopMode()
    {
        $oConfig = $this->getMock(\OxidEsales\Eshop\Core\Config::class, array("isDemoShop"));
        $oConfig->expects($this->once())->method('isDemoShop')->will($this->returnValue(true));

        oxRegistry::getSession()->deleteVariable("Errors");

        $oArtPic = $this->getProxyClass("Article_Pictures");
        Registry::set(Config::class, $oConfig);
        $oArtPic->save();

        $aEx = oxRegistry::getSession()->getVariable("Errors");
        $oEx = unserialize($aEx["default"][0]);

        $this->assertTrue($oEx instanceof ExceptionToDisplay);
    }

    /**
     * Article_Pictures::deletePicture() - in demo shop mode
     *
     * @return null
     */
    public function testDeletePicture_demoShopMode()
    {
        $oConfig = $this->getMock(\OxidEsales\Eshop\Core\Config::class, array("isDemoShop"));
        $oConfig->expects($this->once())->method('isDemoShop')->will($this->returnValue(true));

        oxRegistry::getSession()->deleteVariable("Errors");

        $oArtPic = $this->getProxyClass("Article_Pictures");
        Registry::set(Config::class, $oConfig);
        $oArtPic->deletePicture();

        $aEx = oxRegistry::getSession()->getVariable("Errors");
        $oEx = unserialize($aEx["default"][0]);

        $this->assertTrue($oEx instanceof ExceptionToDisplay);
    }

    /**
     * test for bug#0002041: editing inherited product pictures in subshop changes default shop for product
     */
    public function testSubshopStaysSame()
    {
        $oArticle = $this->getMock(\OxidEsales\Eshop\Application\Model\Article::class, array('load', 'save', 'assign'));
        $oArticle->expects($this->once())->method('load')->with($this->equalTo('asdasdasd'))->will($this->returnValue(true));
        $oArticle->expects($this->once())->method('assign')->with($this->equalTo(array('s' => 'test')))->will($this->returnValue(null));
        $oArticle->expects($this->once())->method('save')->will($this->returnValue(null));

        oxTestModules::addModuleObject('oxarticle', $oArticle);

        $this->setRequestParameter('oxid', 'asdasdasd');
        $this->setRequestParameter('editval', array('s' => 'test'));
        $oArtPic = $this->getProxyClass("Article_Pictures");
        $oArtPic->save();
    }
}
