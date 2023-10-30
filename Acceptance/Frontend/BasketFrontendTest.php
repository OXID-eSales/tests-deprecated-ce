<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Acceptance\Frontend;

use OxidEsales\EshopCommunity\Tests\Acceptance\FrontendTestCase;

/** Tests related creating of orders in frontend. */
class BasketFrontendTest extends FrontendTestCase
{
    /**
     * Vouchers is disabled via performance options
     *
     * @group basketfrontend
     */
    public function testFrontendDisabledVouchers()
    {
        //disabling option (Use vouchers)
        $this->setShopParam("bl_showVouchers", "false", "theme:azure");

        $this->clearCache();
        $this->addToBasket("1000");

        $this->assertElementNotPresent("voucherNr");
        $this->assertElementNotPresent("//button[text()='%SUBMIT_COUPON%']");
        $this->assertTextNotPresent("%ENTER_COUPON_NUMBER%");
    }

    /**
     * Order step 2
     *
     * @group basketfrontend
     */
    public function testFrontendOrderStep2Options()
    {
        $this->addToBasket("1001");
        $this->addToBasket("1002-2");

        //Order Step1
        //checking if order via option 1 (without password) can be disabled
        $this->continueToNextStep();

        //option 1 is available
        $this->assertEquals("%YOU_ARE_HERE%: / %ADDRESS%", $this->getText("breadCrumb"));
        $this->assertElementPresent("optionNoRegistration");
        $this->assertElementPresent("optionRegistration");
        $this->assertElementPresent("optionLogin");

        //checking on option 'Disable order without registration.'
        $this->setShopParam("blOrderDisWithoutReg", "true");
        $this->clickAndWait("link=%STEPS_BASKET%");

        $this->continueToNextStep();

        //Order step2
        //option 1 is not available
        $this->assertEquals("%YOU_ARE_HERE%: / %ADDRESS%", $this->getText("breadCrumb"));
        $this->assertElementNotPresent("//button[text()='%CONTINUE_TO_NEXT_STEP%']");
        $this->assertElementNotPresent("//h3[text()='%BILLING_ADDRESS%']");
        $this->assertElementNotPresent("//h3[text()='%SHIPPING_ADDRESS%']");
        $this->assertElementNotPresent("optionNoRegistration");
        $this->assertElementPresent("optionRegistration");
        $this->assertElementPresent("optionLogin");
        $this->type("//div[@id='optionLogin']//input[@name='lgn_usr']", "example_test@oxid-esales.dev");
        $this->type("//div[@id='optionLogin']//input[@name='lgn_pwd']", "useruser");
        $this->clickAndWait("//div[@id='optionLogin']//button");
        $this->assertEquals("%YOU_ARE_HERE%: / %ADDRESS%", $this->getText("breadCrumb"));
        $this->assertElementPresent("//button[text()='%CONTINUE_TO_NEXT_STEP%']");
        $this->assertTextPresent("%BILLING_ADDRESS%");
        $this->assertTextPresent("%SHIPPING_ADDRESS%");

        $this->continueToNextStep();

        $this->assertEquals("%YOU_ARE_HERE%: / %PAY%", $this->getText("breadCrumb"));
    }

    /**
     * Gift wrapping is disabled via performance options
     *
     * @group basketfrontend
     */
    public function testFrontendDisabledGiftWrapping()
    {
        //disabling option in admin (Use gift wrapping)
        $this->setShopParam("bl_showGiftWrapping", "false", "theme:azure");

        $this->clearCache();
        $this->addToBasket("1001");

        $this->assertEquals("", $this->clearString($this->getText("//tr[@id='cartItem_1']/td[4]")));
        $this->assertElementNotPresent("//tr[@id='cartItem_1']/td[4]/a");
    }

    /**
     * Clicks Continue to Next Step given amount of times.
     *
     * @param int $iSteps
     */
    private function continueToNextStep($iSteps = 1)
    {
        for ($i = 1; $i <= $iSteps; $i++) {
            $this->clickAndWait("//button[text()='%CONTINUE_TO_NEXT_STEP%']");
        }
    }

    /**
     * @param string $sParamName
     * @param string $sParamValue
     * @param null $sModule  optional
     */
    private function setShopParam($sParamName, $sParamValue, $sModule = null)
    {
        $aParams = array("type" => "bool", "value" => $sParamValue);

        if (!is_null($sModule)) {
            $aParams = array_merge($aParams, array("module" => $sModule));
        }

        $this->callShopSC("oxConfig", null, null, array($sParamName => $aParams));
    }
}
