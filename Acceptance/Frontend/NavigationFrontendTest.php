<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Acceptance\Frontend;

use oxDb;
use OxidEsales\EshopCommunity\Tests\Acceptance\FrontendTestCase;

/** Selenium tests for new layout. */
class NavigationFrontendTest extends FrontendTestCase
{
    /**
     * site footer
     *
     * @group frontend
     */
    public function testFrontendFooter()
    {
        $this->openShop();
        $this->assertElementPresent("panel");
        //checking if delivery note is displayed
        $this->assertTextPresent("* %PLUS_SHIPPING%%PLUS_SHIPPING2%");

        //checking if newsletter fields exist. functionality is checked in other test
        $this->assertElementPresent("//div[@id='panel']/div[1]//label[text()='%NEWSLETTER%']");
        $this->assertElementPresent("//div[@id='panel']/div[1]//input[@name='editval[oxuser__oxusername]']");
        $this->assertElementPresent("//div[@id='panel']/div[1]//button[text()='%SUBSCRIBE%']");
        //exit;
        //SERVICE links
        $this->assertElementPresent("footerServices");
        //there are fixed amount of links in here
        $this->assertElementPresent("//dl[@id='footerServices']//dd[9]");
        $this->assertElementNotPresent("//dl[@id='footerServices']//dd[10]");

        $this->clickAndWait("//dl[@id='footerServices']//a[text()='%PAGE_TITLE_CONTACT%']");
        $this->assertEquals("%YOU_ARE_HERE%: / %PAGE_TITLE_CONTACT%", $this->getText("breadCrumb"));
        $this->assertEquals("Your Company Name", $this->getText("//h1"));

        $this->clickAndWait("//dl[@id='footerServices']//a[text()='%HELP%']");
        $this->assertEquals("%YOU_ARE_HERE%: / %HELP% - Main", $this->getText("breadCrumb"));
        $this->assertEquals("%HELP% - Main", $this->getText("//h1"));
        $this->assertTextPresent("Here, you can insert additional information, further links, user manual etc");

        $this->clickAndWait("//dl[@id='footerServices']//a[text()='%LINKS%']");
        $this->assertEquals("%YOU_ARE_HERE%: / %LINKS%", $this->getText("breadCrumb"));
        $this->assertEquals("%LINKS%", $this->getText("//h1"));
        $this->assertTextPresent("Demo link description [EN] šÄßüл");

        $this->clickAndWait("//dl[@id='footerServices']//a[text()='%ACCOUNT%']");
        $this->assertEquals("%YOU_ARE_HERE%: / %LOGIN%", $this->getText("breadCrumb"));
        $this->assertEquals("%PAGE_TITLE_ACCOUNT%", $this->getText("//h1"));

        $this->assertElementPresent("//section[@id='content']//input[@name='lgn_usr']");
        $this->clickAndWait("//dl[@id='footerServices']//a[text()='%WISH_LIST%']");
        $this->assertEquals("%YOU_ARE_HERE%: / %MY_ACCOUNT% / %MY_WISH_LIST%", $this->getText("breadCrumb"));
        $this->assertEquals("%PAGE_TITLE_ACCOUNT_NOTICELIST%", $this->getText("//h1"));
        $this->assertElementPresent("//section[@id='content']//input[@name='lgn_usr']");

        $this->clickAndWait("//dl[@id='footerServices']//a[text()='%MY_GIFT_REGISTRY%']");
        $this->assertEquals("%YOU_ARE_HERE%: / %MY_ACCOUNT% / %MY_GIFT_REGISTRY%", $this->getText("breadCrumb"));
        $this->assertEquals("%PAGE_TITLE_ACCOUNT_WISHLIST%", $this->getText("//h1"));
        $this->assertElementPresent("//section[@id='content']//input[@name='lgn_usr']");

        $this->clickAndWait("//dl[@id='footerServices']//a[text()='%PUBLIC_GIFT_REGISTRIES%']");
        $this->assertEquals("%YOU_ARE_HERE%: / %PUBLIC_GIFT_REGISTRIES%", $this->getText("breadCrumb"));
        $this->assertEquals("%PUBLIC_GIFT_REGISTRIES%", $this->getText("//h1"));
        $this->assertTextPresent("%SEARCH_GIFT_REGISTRY%");
        $this->isElementPresent("search");

        $this->clickAndWait("//dl[@id='footerServices']//a[text()='%PAGE_TITLE_BASKET%']");
        $this->assertEquals("%YOU_ARE_HERE%: / %PAGE_TITLE_BASKET%", $this->getText("breadCrumb"));
        $this->assertTextPresent("%BASKET_EMPTY%");

        $this->clickAndWait("//dl[@id='footerInformation']//a[text()='About Us']");
        $this->assertEquals("%YOU_ARE_HERE%: / About Us", $this->getText("breadCrumb"));
        $this->assertEquals("About Us", $this->getText("//h1"));
        $this->assertTextPresent("Add provider identification here.");

        $this->clickAndWait("//dl[@id='footerInformation']//a[text()='Terms and Conditions']");
        $this->assertEquals("%YOU_ARE_HERE%: / Terms and Conditions", $this->getText("breadCrumb"));
        $this->assertEquals("Terms and Conditions", $this->getText("//h1"));
        $this->assertTextPresent("Insert your terms and conditions here.");

        $this->clickAndWait("//dl[@id='footerInformation']//a[text()='Privacy Policy']");
        $this->assertEquals("%YOU_ARE_HERE%: / Privacy Policy", $this->getText("breadCrumb"));
        $this->assertEquals("Privacy Policy", $this->getText("//h1"));
        $this->assertTextPresent("Enter your privacy policy here.");

        $this->clickAndWait("//dl[@id='footerInformation']//a[text()='Shipping and Charges']");
        $this->assertEquals("%YOU_ARE_HERE%: / Shipping and Charges", $this->getText("breadCrumb"));
        $this->assertEquals("Shipping and Charges", $this->getText("//h1"));
        $this->assertTextPresent("Add your shipping information and costs here.");

        $this->clickAndWait("//dl[@id='footerInformation']//a[text()='Right of Withdrawal']");
        $this->assertEquals("%YOU_ARE_HERE%: / Right of Withdrawal", $this->getText("breadCrumb"));
        $this->assertEquals("Right of Withdrawal", $this->getText("//h1"));
        $this->assertTextPresent("Insert here the Right of Withdrawal policy");

        $this->clickAndWait("//dl[@id='footerInformation']//a[text()='How to order?']");
        $this->assertEquals("%YOU_ARE_HERE%: / How to order?", $this->getText("breadCrumb"));
        $this->assertEquals("How to order?", $this->getText("//h1"));
        $this->assertTextPresent("Text Example");

        $this->clickAndWait("//dl[@id='footerInformation']//a[text()='Credits']");
        $this->assertEquals("%YOU_ARE_HERE%: / Credits", $this->getText("breadCrumb"));
        $this->assertEquals("Credits", $this->getText("//h1"));
        $this->assertTextPresent("Please add your text here");

        $this->clickAndWait("//dl[@id='footerInformation']//a[text()='%NEWSLETTER%']");
        $this->assertEquals("%YOU_ARE_HERE%: / %STAY_INFORMED%", $this->getText("breadCrumb"));
        $this->assertEquals("%STAY_INFORMED%", $this->getText("//h1"));
        $this->assertTextPresent("You can unsubscribe any time from the newsletter.");

        $this->clickAndWait("link=%HOME%");
        $this->assertElementNotPresent("breadCrumb");
        $this->assertTextPresent("%JUST_ARRIVED%");

        //MANUFACTURERS links
        $this->assertElementPresent("footerManufacturers");
        $this->clickAndWait("//dl[@id='footerManufacturers']//a[text()='Manufacturer [EN] šÄßüл']");
        $this->assertEquals("%YOU_ARE_HERE%: / %BY_MANUFACTURER% / Manufacturer [EN] šÄßüл", $this->getText("breadCrumb"));
        $this->assertEquals("Manufacturer [EN] šÄßüл", $this->getText("//h1"));
        $this->selectDropDown("viewOptions", "%line%");
        $this->assertElementPresent("//ul[@id='productList']/li[1]//a[@id='productList_1']");
        $this->assertEquals("Test product 0 [EN] šÄßüл", $this->getText("productList_1"));
        $this->assertElementPresent("//ul[@id='productList']/li[4]");
        $this->assertElementNotPresent("//ul[@id='productList']/li[5]");

        //CATEGORIES links
        $this->assertElementPresent("footerCategories");
        $this->assertEquals("Test category 0 [EN] šÄßüл", $this->clearString($this->getText("//dl[@id='footerCategories']//dd[2]")));
        $this->clickAndWait("//dl[@id='footerCategories']//dd[2]/a");
        $this->assertEquals("Test category 0 [EN] šÄßüл", $this->getHeadingText("//h1"));
        $this->assertElementPresent("//ul[@id='productList']/li[1]//a[@id='productList_1']");
        $this->assertEquals("Test product 0 [EN] šÄßüл", $this->getText("productList_1"));
        $this->assertElementPresent("//ul[@id='productList']/li[2]");
        $this->assertElementNotPresent("//ul[@id='productList']/li[3]");
    }

    /**
     * Checking Top Menu Navigation
     *
     * @group frontend
     */
    public function testFrontendTopMenu()
    {
        $this->openShop(false, true);
        $this->assertTrue($this->isVisible("navigation"));
        $this->assertEquals("%HOME%", $this->clearString($this->getText("//ul[@id='navigation']/li[1]")));
        $this->assertEquals("Test category 0 [EN] šÄßüл »", $this->clearString($this->getText("//ul[@id='navigation']/li[3]/a")));
        $this->assertElementNotPresent("//ul[@id='tree']/li");
        $this->clickAndWait("//ul[@id='navigation']/li[3]/a");

        $this->assertEquals("Test category 0 [EN] šÄßüл", $this->getHeadingText("//h1"));
        $this->assertEquals("%YOU_ARE_HERE%: / Test category 0 [EN] šÄßüл", $this->getText("breadCrumb"));
        $this->assertElementPresent("//ul[@id='tree']/li");
        $this->assertEquals("Test category 0 [EN] šÄßüл", $this->clearString($this->getText("//ul[@id='tree']/li/a")));
        $this->assertEquals("Test category 1 [EN] šÄßüл", $this->clearString($this->getText("//ul[@id='tree']/li/ul/li/a")));
        $this->selectDropDown("viewOptions", "%line%");
        $this->assertElementPresent("productList_1");
        $this->assertElementPresent("productList_2");
        $this->assertElementPresent("//ul[@id='productList']/li[2]");
        $this->assertElementNotPresent("//ul[@id='productList']/li[3]");

        $this->clickAndWait("//ul[@id='tree']/li/ul/li/a");
        $this->assertElementPresent("//ul[@id='tree']/li");
        $this->assertEquals("Test category 0 [EN] šÄßüл", $this->clearString($this->getText("//ul[@id='tree']/li/a")));
        $this->assertEquals("Test category 1 [EN] šÄßüл", $this->clearString($this->getText("//ul[@id='tree']/li/ul/li/a")));
        $this->assertEquals("Test category 1 [EN] šÄßüл", $this->getHeadingText("//h1"));
        $this->assertEquals("%YOU_ARE_HERE%: / Test category 0 [EN] šÄßüл / Test category 1 [EN] šÄßüл", $this->getText("breadCrumb"));
        $this->assertElementPresent("productList_1");
        $this->assertElementPresent("productList_2");

        $this->assertElementPresent("//ul[@id='productList']/li[2]");
        $this->assertElementNotPresent("//ul[@id='productList']/li[3]");

        //more
        $this->clickAndWait("//ul[@id='navigation']/li[4]/a");
        $this->assertElementPresent("//ul[@id='navigation']/li[4]");
        $this->assertElementPresent("//ul[@id='navigation']/li[5]");
        //exit;
        $this->assertEquals("%MORE% »", $this->getText("//ul[@id='navigation']/li[5]/a"));
        $this->assertElementNotPresent("//ul[@id='navigation']/li[6]");
        //checking on option (Amount of categories that is displayed at top) if is used value = 4
        $this->callShopSC("oxConfig", null, null, array("iTopNaviCatCount" => array("type" => "str", "value" => '5', "module" => "theme:azure")));
        $this->clearCache();
        $this->openShop();
        $this->assertElementPresent("//ul[@id='navigation']/li[6]");
        $this->assertEquals("%MORE% »", $this->getText("//ul[@id='navigation']/li[7]/a"));
        $this->assertElementNotPresent("//ul[@id='navigation']/li[8]");
    }

    /**
     * Checking Performance options
     * option: Load Selection Lists in Product Lists
     * option: Support Price Modifications by Selection Lists
     * option: Load Selection Lists
     *
     * @group frontend
     */
    public function testFrontendPerfOptionsSelectionLists()
    {
        $this->openShop();
        $this->searchFor("1001");
        $this->selectDropDown("viewOptions", "%line%");
        $this->assertElementPresent("selectlistsselector_searchList_1");
        //page details. selection lists are with prices
        $this->assertEquals("test selection list [EN] šÄßüл: selvar1 [EN] šÄßüл +1,00 € selvar1 [EN] šÄßüл +1,00 € selvar2 [EN] šÄßüл selvar3 [EN] šÄßüл -2,00 € selvar4 [EN] šÄßüл +2%", $this->clearString($this->getText("selectlistsselector_searchList_1")));
        $this->clickAndWait("link=Test category 0 [EN] šÄßüл");
        $this->assertEquals("test selection list [EN] šÄßüл: selvar1 [EN] šÄßüл +1,00 € selvar1 [EN] šÄßüл +1,00 € selvar2 [EN] šÄßüл selvar3 [EN] šÄßüл -2,00 € selvar4 [EN] šÄßüл +2%", $this->clearString($this->getText("selectlistsselector_productList_2")));

        //option (Support Price Modifications by Selection Lists) is OFF
        $this->callShopSC("oxConfig", null, null, array("bl_perfUseSelectlistPrice" => array("type" => "bool", "value" => "false")));

        $this->clearCache();
        $this->openShop();
        $this->searchFor("1001");
        $this->selectDropDown("viewOptions", "%line%");
        $this->assertEquals("test selection list [EN] šÄßüл: selvar1 [EN] šÄßüл selvar1 [EN] šÄßüл selvar2 [EN] šÄßüл selvar3 [EN] šÄßüл selvar4 [EN] šÄßüл", $this->clearString($this->getText("selectlistsselector_searchList_1")));
        $this->clickAndWait("link=Test category 0 [EN] šÄßüл");
        $this->assertEquals("test selection list [EN] šÄßüл: selvar1 [EN] šÄßüл selvar1 [EN] šÄßüл selvar2 [EN] šÄßüл selvar3 [EN] šÄßüл selvar4 [EN] šÄßüл", $this->clearString($this->getText("selectlistsselector_productList_2")));

        // loading selection lists in product lists is OFF
        $this->callShopSC("oxConfig", null, null, array("bl_perfLoadSelectListsInAList" => array("type" => "bool", "value" => "false")));

        $this->clearCache();
        $this->openShop();
        $this->clickAndWait("link=Test category 0 [EN] šÄßüл");
        $this->selectDropDown("viewOptions", "%line%");
        $this->assertElementNotPresent("selectlistsselector_productList_2");
        $this->clickAndWait("//ul[@id='productList']/li[2]//a");
        $this->assertEquals("test selection list [EN] šÄßüл: selvar1 [EN] šÄßüл selvar1 [EN] šÄßüл selvar2 [EN] šÄßüл selvar3 [EN] šÄßüл selvar4 [EN] šÄßüл", $this->clearString($this->getText("productSelections")));

        //loading selection lists is OFF
        $this->callShopSC("oxConfig", null, null, array("bl_perfLoadSelectLists" => array("type" => "bool", "value" => "false")));

        $this->clearCache();
        $this->openShop();
        $this->clickAndWait("link=Test category 0 [EN] šÄßüл");
        $this->selectDropDown("viewOptions", "%line%");
        $this->assertElementNotPresent("selectlistsselector_productList_2");
        $this->clickAndWait("//ul[@id='productList']/li[2]//a");
        $this->assertElementNotPresent("productSelections");
    }

    /**
     * Checking contact sending
     *
     * @group frontend
     */
    public function testFrontendContact()
    {
        //In admin Set option (Installed GDLib Version) if "value" => ""
        $this->callShopSC("oxConfig", null, null, array("iUseGDVersion" => array("type" => "str", "value" => 2)));

        $this->clearCache();
        $this->openShop();
        $this->clickAndWait("//dl[@id='footerServices']//a[text()='%PAGE_TITLE_CONTACT%']");
        $this->assertEquals("%YOU_ARE_HERE%: / %PAGE_TITLE_CONTACT%", $this->getText("breadCrumb"));
        $this->assertEquals("Your Company Name", $this->getText("//h1"));
        $this->assertEquals("%MR% %MRS%", $this->clearString($this->getText("editval[oxuser__oxsal]")));
        $this->select("editval[oxuser__oxsal]", "label=%MRS%");
        $this->type("editval[oxuser__oxfname]", "first name");
        $this->type("editval[oxuser__oxlname]", "");
        $this->type("contactEmail", "example_test@oxid-esales.dev");
        $this->type("c_subject", "subject");
        $this->type("c_message", "message text");
        $this->click("//button[text()='%SEND%']");
        $this->waitForText("%ERROR_MESSAGE_INPUT_NOTALLFIELDS%");

        $this->assertEquals("%MRS%", $this->getSelectedLabel("editval[oxuser__oxsal]"));
        $this->assertEquals("first name", $this->getValue("editval[oxuser__oxfname]"));
        $this->type("editval[oxuser__oxfname]", "");
        $this->type("editval[oxuser__oxlname]", "last name");
        $this->assertEquals("example_test@oxid-esales.dev", $this->getValue("contactEmail"));
        $this->assertEquals("subject", $this->getValue("c_subject"));
        $this->assertEquals("message text", $this->getValue("c_message"));
        $this->click("//button[text()='%SEND%']");
        $this->waitForText("%ERROR_MESSAGE_INPUT_NOTALLFIELDS%");

        $this->assertEquals("%MRS%", $this->getSelectedLabel("editval[oxuser__oxsal]"));
        $this->type("editval[oxuser__oxfname]", "first name");
        $this->assertEquals("last name", $this->getValue("editval[oxuser__oxlname]"));
        $this->assertEquals("example_test@oxid-esales.dev", $this->getValue("contactEmail"));
        $this->assertEquals("subject", $this->getValue("c_subject"));
        $this->assertEquals("message text", $this->getValue("c_message"));
        $this->clickAndWait("//button[text()='%SEND%']");
        $this->assertTextPresent("%THANK_YOU%");
        $this->assertEquals("%YOU_ARE_HERE%: / %PAGE_TITLE_CONTACT%", $this->getText("breadCrumb"));
    }

    /**
     * Checking CMS pages marked as categories
     *
     * @group frontend
     */
    public function testFrontendCmsAsCategories()
    {
        //activating CMS pages as categories
        //TODO: Selenium refactor to remove SQL's executions
        $this->executeSql("UPDATE `oxcontents` SET `OXACTIVE`=1, `OXACTIVE_1`=1 WHERE `OXID` = 'testcontent1' OR `OXID` = 'testcontent2' OR `OXID` = 'oxsubshopcontent1' OR `OXID` = 'oxsubshopcontent2'");

        //cms as root category
        $this->clearCache();
        $this->openShop();
        $this->assertEquals("[last] [EN] content šÄßüл", $this->clearString($this->getText("//ul[@id='navigation']/li[3]")));
        $this->clickAndWait("//ul[@id='navigation']/li[3]//a");
        $this->assertEquals("%YOU_ARE_HERE%: / [last] [EN] content šÄßüл", $this->getText("breadCrumb"));
        $this->assertEquals("[last] [EN] content šÄßüл", $this->getText("//h1"));
        $this->assertTextPresent("content [EN] 1 šÄßüл");

        //cms as subcategory
        $this->assertEquals("[last] [EN] content šÄßüл", $this->clearString($this->getText("//ul[@id='navigation']/li[3]")));
        $this->clickAndWait("//ul[@id='navigation']/li[5]/a");
        $this->assertEquals("%CATEGORY_OVERVIEW%", $this->getHeadingText("//h1"));
        $this->clickAndWait("moreSubCat_2");
        $this->assertEquals("Test category 0 [EN] šÄßüл", $this->getHeadingText("//h1"));
        $this->assertEquals("3 [EN] content šÄßüл", $this->clearString($this->getText("//ul[@id='tree']/li[1]/ul/li[1]")));
        $this->assertElementPresent("//a[@id='moreSubCms_1_1']/@title", "attribute title is gone from link. in 450 it was for category names, that were shortened");
        $this->assertEquals("3 [EN] content šÄßüл", $this->getAttribute("//a[@id='moreSubCms_1_1']/@title"), "bug from Mantis #495");
        $this->clickAndWait("moreSubCms_1_1");
        $this->assertEquals("%YOU_ARE_HERE%: / 3 [EN] content šÄßüл", $this->getText("breadCrumb"));
        $this->assertEquals("3 [EN] content šÄßüл", $this->getText("//h1"));
        $this->assertTextPresent("content [EN] last šÄßüл");
        $this->assertEquals("3 [EN] content šÄßüл", $this->clearString($this->getText("//ul[@id='tree']/li[1]/ul/li[1]")));
    }

    /**
     * Promotions in frontend. Categories
     *
     * @group frontend
     */
    public function testFrontendPromotionsCategories()
    {
        if (isSUBSHOP) {
            $this->markTestSkipped('This test case is only actual when SubShops are available.');
        }
        $this->clearCache();
        $this->openShop();
        //Categories
        $this->assertElementPresent("//div[@id='specCatBox']/h2");
        $this->assertEquals("Wakeboards", $this->getText("//div[@id='specCatBox']/h2"));
        //fix it in future: mouseOver effect is implemented via css. selenium does not support it yet
        $this->clickAndWait("//div[@id='specCatBox']/a");
        $this->assertEquals("%YOU_ARE_HERE%: / Wakeboarding / Wakeboards", $this->getText("breadCrumb"));
        $this->assertEquals("Wakeboards", $this->getHeadingText("//h1"));
        $this->assertElementPresent("//ul[@id='productList']/li[1]");
    }

    /**
     * Checking Performance options
     *
     * @group frontend
     * @group frontend_performance
     */
    public function testFrontendPerfOptions1()
    {
        $this->openShop();
        $this->assertEquals("50,00 € *", $this->getText("//ul[@id='newItems']/li[1]//span[@class='price']"));
        $this->assertEquals("50,00 € *", $this->getText("//div[@id='topBox']/ul/li[2]//strong"));

        // option -> performance-> "Display Number of contained Products behind Category Names"
        $this->callShopSC("oxConfig", null, null, array("bl_perfShowActionCatArticleCnt" => array("type" => "bool", "value" => true)));
        // option -> performance-> "Calculate Shipping Costs"
        $this->callShopSC("oxConfig", null, null, array("bl_perfLoadDelivery" => array("type" => "bool", "value" => false)));
        // option ->performance -> " Show Prices in "Top of the Shop" and "Just arrived!" "
        $this->callShopSC("oxConfig", null, null, array("bl_perfLoadPriceForAddList" => array("type" => "bool", "value" => false)));

        $this->clearCache();
        $this->openShop();
        $this->assertElementNotPresent("//ul[@id='newItems']/li[1]//strong[text()='50,00 € *']");
        $this->assertElementNotPresent("//div[@id='topBox']/ul/li[1]//strong[text()='50,00 €']");
        $this->assertEquals("Test category 0 [EN] šÄßüл (2) »", $this->getText("//ul[@id='navigation']/li[3]"));

        $this->clickAndWait("//ul[@id='navigation']/li[3]/a");
        $this->assertEquals("Test category 0 [EN] šÄßüл (2)", $this->clearString($this->getText("//ul[@id='tree']/li[1]/a")));
        $this->assertEquals("Test category 1 [EN] šÄßüл (2)", $this->clearString($this->getText("//ul[@id='tree']/li[1]/ul/li")));
        $this->assertEquals("Test category 1 [EN] šÄßüл", $this->getAttribute("//a[@id='moreSubCat_1']@title"));
        $this->assertEquals("(2)", substr($this->getText("moreSubCat_1"), -3));

        $this->clickAndWait("link=Home");
        $this->loginInFrontend("example_test@oxid-esales.dev", "useruser");

        $this->addToBasket('1001', 1, 'user');

        $this->clickAndWait("//button[text()='%CONTINUE_TO_NEXT_STEP%']");

        //option -> performance->"Activate user Reviews and Ratings"
        $this->callShopSC("oxConfig", null, null, array("bl_perfLoadReviews" => array("type" => "bool", "value" => "false")));
        //option -> performance->"Calculate Product Price"
        $this->callShopSC("oxConfig", null, null, array("bl_perfLoadPrice" => array("type" => "bool", "value" => "false")));
        //option -> performance->"Load similar Products"
        $this->callShopSC("oxConfig", null, null, array("bl_perfLoadSimilar" => array("type" => "bool", "value" => "false")));
        //option -> performance->"  Load Crossselling "
        $this->callShopSC("oxConfig", null, null, array("bl_perfLoadCrossselling" => array("type" => "bool", "value" => "false")));
        //option -> performance->"Load Accessories "
        $this->callShopSC("oxConfig", null, null, array("bl_perfLoadAccessoires" => array("type" => "bool", "value" => "false")));
        //theme option-> "Use compare list"
        $this->callShopSC("oxConfig", null, null, array("bl_showCompareList" => array("type" => "bool", "value" => "false", "module" => "theme:azure")));

        $this->openArticle(1002);
        $this->assertTextNotPresent("review for parent product šÄßüл");
        $this->openArticle(1000);

        $this->assertElementNotPresent("productPrice");
        $this->assertElementNotPresent("similar");
        $this->assertElementNotPresent("cross");
        $this->assertElementNotPresent("accessories");

        $this->click("productLinks");
        $this->assertElementNotPresent("addToCompare");

        $this->clickAndWait("//dl[@id='footerServices']//a[text()='%ACCOUNT%']");
        $this->assertElementNotPresent("link=%MY_PRODUCT_COMPARISON%");

        // option -> performance->"Load Promotions"
        $this->callShopSC("oxConfig", null, null, array("bl_perfLoadAktion" => array("type" => "bool", "value" => "false")));
        //option -> performance->" Display Currencies"
        $this->callShopSC("oxConfig", null, null, array("bl_perfLoadCurrency" => array("type" => "bool", "value" => "false")));
        //option -> performance->" Display Languages"
        $this->callShopSC("oxConfig", null, null, array("bl_perfLoadLanguages" => array("type" => "bool", "value" => "false")));

        $this->clearCache();
        $this->openShop();
        $this->assertElementNotPresent("titleBargain_1");
        $this->assertElementNotPresent("//div[@id='specCatBox']/h2");
        $this->assertElementNotPresent("topBox");
        $this->assertElementNotPresent("newItems");
        $this->assertElementNotPresent("currencyTrigger");
        $this->assertElementNotPresent("languageTrigger");
    }

    /**
     * Promotions in frontend. week's special
     *
     * @group frontend
     */
    public function testFrontendPromotionsWeekSpecial()
    {
        if (isSUBSHOP) {
            $this->callShopSC("oxActions", "save", 'oxbargain', array('oxshopid' => oxSHOPID), null, 1);
        }

        $aWeeksSpecialArticleParameters = array(
            'oxshopid' => oxSHOPID,
            'oxactionid' => 'oxbargain',
            'oxartid' => '1000',
            'oxsort' => '-1'
        );
        $this->callShopSC("oxBase", "save", 'oxactions2article', $aWeeksSpecialArticleParameters);

        $this->openShop();
        $this->assertEquals("Test product 0 [EN] šÄßüл", $this->getText("//div[@id='specBox']/div/a"));
        $this->clickAndWait("//div[@id='specBox']/div/a");
        $this->assertEquals("Test product 0 [EN] šÄßüл", $this->getText("//h1"));
        $this->clickAndWait("link=%HOME%");
        $this->assertEquals("50,00 € *", $this->getText("//div[@id='priceBargain_1']//span"));
        $this->assertEquals("%TO_CART%", $this->clearString($this->getText("//div[@id='priceBargain_1']//a")));
        $this->clickAndWait("//div[@id='priceBargain_1']//a");
        $this->openBasket();
        $this->assertElementPresent("//tr[@id='cartItem_1']//a/b[text()='Test product 0 [EN] šÄßüл']");

        // Remove from week's special added article
        $this->callShopSC('oxActions', 'removeArticle', 'oxbargain', null, array('1000'));
        $this->clearCache();
        $this->openShop();
        $this->clickAndWait("link=%HOME%");
        $this->assertEquals("%WEEK_SPECIAL%", $this->getHeadingText("//div[@id='specBox']//h3"));
        $this->assertEquals("Test product 1 [EN] šÄßüл", $this->getText("//div[@id='specBox']/div/a"));
        $this->clickAndWait("//div[@id='specBox']/div/a");
        $this->assertEquals("Test product 1 [EN] šÄßüл", $this->getText("//h1"));
        $this->clickAndWait("link=%HOME%");
        //fix it in future: mouseOver effect is implemented via css. Selenium does not support it yet
        $this->assertEquals("%REDUCED_FROM_2% 150,00 €", $this->getText("//div[@id='priceBargain_1']//span"));
        $this->assertEquals("100,00 €", $this->getText("//div[@id='priceBargain_1']//span[2]"));
        $this->assertEquals("%MORE_INFO%", $this->clearString($this->getText("//div[@id='priceBargain_1']//a")));
        $this->clickAndWait("//div[@id='priceBargain_1']//a");
        $this->assertEquals("Test product 1 [EN] šÄßüл", $this->getText("//h1"));
    }

    /**
     * checking if variants are displayed correctly in list
     *
     * @group frontend
     */
    public function testVariantsInLists()
    {
        $this->openShop();
        $this->searchFor("3570 1002");
        $this->assertEquals("2 %HITS_FOR% \"3570 1002\"", $this->getHeadingText("//h1"));
        $this->assertElementPresent("searchList_1");
        $this->assertElementPresent("searchList_2");
        $this->assertElementNotPresent("searchList_3");
        //double grid view
        $this->assertElementPresent("//form[@name='tobasketsearchList_1']//a[text()='%MORE_INFO%']");
        $this->assertElementPresent("//form[@name='tobasketsearchList_2']//a[text()='%MORE_INFO%']");
        $this->clickAndWait("//form[@name='tobasketsearchList_1']//a[text()='%MORE_INFO%']");

        $this->assertEquals("Test product 2 [EN] šÄßüл", $this->getText("//h1"));
        $this->clickAndWait("//div[@id='overviewLink']/a");
        $this->waitForElement("searchList");
        $this->assertEquals("2 %HITS_FOR% \"3570 1002\"", $this->getHeadingText("//h1"));
        $this->clickAndWait("//form[@name='tobasketsearchList_2']//a[text()='%MORE_INFO%']");
        $this->assertEquals("Kuyichi Jeans ANNA", $this->getText("//h1"));

        $this->searchFor("3570 1002");
        $this->assertEquals("2 %HITS_FOR% \"3570 1002\"", $this->getHeadingText("//h1"));
        //line view
        $this->selectDropDown("viewOptions", "%line%");
        $this->assertElementPresent("//ul[@id='searchList']/li[1]//a[text()='%MORE_INFO%']");
        $this->assertElementPresent("//ul[@id='searchList']/li[2]//a[text()='%MORE_INFO%']");
        $this->clickAndWait("//ul[@id='searchList']/li[2]//a[text()='%MORE_INFO%']");
        $this->assertEquals("Kuyichi Jeans ANNA", $this->getText("//h1"));

        $this->searchFor("3570 1002");
        $this->assertEquals("2 %HITS_FOR% \"3570 1002\"", $this->getHeadingText("//h1"));
        //grid view
        $this->selectDropDown("viewOptions", "%grid%");
        $this->assertElementPresent("//ul[@id='searchList']/li[1]//img");
        //fix it in future: mouseOver effect is is implemented via css. Selenium does not support it yet
        //$this->mouseOverAndClick("//ul[@id='searchList']/li[1]//img", "//ul[@id='searchList']/li[1]//a[text()='more Info']");
        $this->clickAndWait("//ul[@id='searchList']/li[1]//a[text()='%MORE_INFO%']");
        $this->assertEquals("Test product 2 [EN] šÄßüл", $this->getText("//h1"));
        $this->clickAndWait("//div[@id='overviewLink']/a");
        $this->waitForElement("searchList");
        $this->assertEquals("2 %HITS_FOR% \"3570 1002\"", $this->getHeadingText("//h1"));
        //fix it in future: mouseOver effect is implemented via css. Selenium does not support it yet.
        //$this->mouseOverAndClick("//ul[@id='searchList']/li[2]//img", "//ul[@id='searchList']/li[2]//a[text()='more Info']");
        $this->clickAndWait("//ul[@id='searchList']/li[2]//a[text()='%MORE_INFO%']");
        $this->assertEquals("Kuyichi Jeans ANNA", $this->getText("//h1"));

        //Check functionality if "Load Variants in Lists" is disabled in admin area
        $this->callShopSC("oxConfig", null, null, array("blLoadVariants" => array("type" => "bool", "value" => "false")));

        $this->clearCache();
        $this->openShop();
        $this->searchFor("3570");
        $this->assertElementPresent("link=Kuyichi Jeans ANNA");
        $this->assertElementPresent("link=%CHOOSE_VARIANT%");
        $this->clickAndWait("link=%CHOOSE_VARIANT% ");
        $this->assertTextPresent("%DETAILS_CHOOSEVARIANT%");
    }

    /**
     * Testing Cookie solution. Is Message appears in frontend about cookies saving
     *
     * @group frontend
     */

    /**
     * @param $iStatus
     * @return array
     */
    protected function userData($iStatus)
    {
        $aSubscribedUserData = array(
            'OXSAL' => 'MRS',
            'OXFNAME' => 'name_šÄßüл',
            'OXLNAME' => 'surname_šÄßüл',
            'OXEMAIL' => 'example01@oxid-esales.dev',
            'OXDBOPTIN' => (string) $iStatus
        );

        return $aSubscribedUserData;
    }

    /**
     * Check article list element.
     */
    private function checkArticleList()
    {
        $this->checkFilter();
        $this->assertElementPresent("//ul[@id='productList']/li[1]");
        $this->assertElementPresent("//ul[@id='productList']/li[4]");
        $this->assertElementNotPresent("//ul[@id='productList']/li[5]");
    }

    /**
     * Checks filter.
     */
    private function checkFilter()
    {
        $this->assertElementPresent("itemsPerPage");
        $this->assertElementPresent("sortItems");
        $this->assertElementPresent("viewOptions");
    }

    /**
     * Checks navigation elements.
     *
     * @param $sNavigationId
     * @param $blButtonNextIsVisible
     * @param $blButtonPreviousIsVisible
     */
    private function checkNavigation($sNavigationId, $blButtonNextIsVisible, $blButtonPreviousIsVisible)
    {
        $this->assertElementPresent("//div[@id='$sNavigationId']//a[text()='1']");
        $this->assertElementPresent("//div[@id='$sNavigationId']//a[text()='2']");
        $this->checkNextPreviousButtons($sNavigationId, $blButtonNextIsVisible, '%NEXT%');
        $this->checkNextPreviousButtons($sNavigationId, $blButtonPreviousIsVisible, '%PREVIOUS%');
        $this->assertElementPresent("productList_1");
        $this->assertElementNotPresent("//ul[@id='productList']/li[2]");
    }

    /**
     * Checks if button visible or not.
     *
     * @param $sNavigationId
     * @param $blButtonVisible
     * @param $sButtonName
     */
    private function checkNextPreviousButtons($sNavigationId, $blButtonVisible, $sButtonName)
    {
        if ($blButtonVisible) {
            $this->assertElementPresent("//div[@id='$sNavigationId']//a[text()='$sButtonName']");
        } else {
            $this->assertElementNotPresent("//div[@id='$sNavigationId']//a[text()='$sButtonName']");
        }
    }

    /**
     * Checks if article in list has buying button.
     *
     * @param null $sButtonText
     */
    private function checkIfPossibleToBuyItemFromList($sButtonText = null)
    {
        $this->assertElementNotPresent("amountToBasket_productList_1");
        $this->assertElementNotPresent("//form[@name='tobasket.productList_1']//input[@name='aid' and @value='1000']");
        if (is_null($sButtonText)) {
            $this->assertElementNotPresent("//form[@name='tobasket.productList_1']//button");
        } else {
            $this->assertElementNotPresent("//form[@name='tobasket.productList_1']//button[text()='$sButtonText']");
        }
    }
}
