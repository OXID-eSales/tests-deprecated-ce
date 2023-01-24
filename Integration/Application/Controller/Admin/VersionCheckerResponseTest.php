<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace Integration\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\ShopLicense;
use OxidEsales\Eshop\Core\Curl;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\Eshop\Core\UtilsObject;
use OxidEsales\TestingLibrary\UnitTestCase;

class VersionCheckerResponseTest extends UnitTestCase
{
    public function testDocumentationLinkShouldBeInserted(): void
    {
        $curlMock = $this->createMock(Curl::class);
        $curlMock->method('execute')->willReturn('<font face="Arial,Verdana,Geneva,Arial,Helvetica,sans-serif">
	OXID eShop Update Status:<br/><br/>
    Your OXID eShop Version is: <b>7.0.0</b><br/>
    Latest OXID eShop Version is: <b>16.5.1</b><br/><br/>

	<b>Your OXID eShop Version is unknown to us. This may mean that you are using a pre-release version that is not yet officially released.</b><br/>
</font>');

        UtilsObject::setClassInstance(Curl::class, $curlMock);
        $controller = oxNew(ShopLicense::class);
        $controller->render();
        $versionInfo = $controller->getViewData()['aCurVersionInfo'];

        $this->assertStringContainsString(Registry::getLang()->translateString('VERSION_UPDATE_LINK'), $versionInfo);

        $expectedNormalizedVersionInfo =
            "
	OXID eShop Update Status:<br><br>
    Your OXID eShop Version is: <b>7.0.0</b><br>
    Latest OXID eShop Version is: <b>16.5.1</b><br><br><a id='linkToUpdate' href='http://www.oxid-esales.com/de/support-services/dokumentation-und-hilfe/oxid-eshop/installation/oxid-eshop-aktualisieren/update-vorbereiten.html' target='_blank'>

	<b>Your OXID eShop Version is unknown to us. This may mean that you are using a pre-release version that is not yet officially released.</b></a><br>
";
        $this->assertSame($expectedNormalizedVersionInfo, $versionInfo);
    }

    public function testResponseWithoutDocumentationLink(): void
    {
        $curlMock = $this->createMock(Curl::class);
        $curlMock->method('execute')->willReturn('<font face="Arial,Verdana,Geneva,Arial,Helvetica,sans-serif">
	OXID eShop Update Status:<br/><br/>
    Your OXID eShop Version is: <b>7.0.0</b><br/>
    Latest OXID eShop Version is: <b>6.5.1</b><br/><br/>

	<b>Your OXID eShop Version is unknown to us. This may mean that you are using a pre-release version that is not yet officially released.</b><br/>
</font>');

        UtilsObject::setClassInstance(Curl::class, $curlMock);
        $controller = oxNew(ShopLicense::class);
        $controller->render();
        $versionInfo = $controller->getViewData()['aCurVersionInfo'];

        $expectedNormalizedVersionInfo =
            "
	OXID eShop Update Status:<br><br>
    Your OXID eShop Version is: <b>7.0.0</b><br>
    Latest OXID eShop Version is: <b>6.5.1</b><br><br>

	<b>Your OXID eShop Version is unknown to us. This may mean that you are using a pre-release version that is not yet officially released.</b><br>
";
        $this->assertSame($expectedNormalizedVersionInfo, $versionInfo);
    }
}
