<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Core;

use OxidEsales\Eshop\Core\UtilsView;
use OxidEsales\EshopCommunity\Core\Registry;
use OxidEsales\TestingLibrary\UnitTestCase;

final class UtilsViewTest extends UnitTestCase
{
    /** @var string */
    private string $smartyUnparsedContent = '[{1|cat:2|cat:3}]';
    /** @var string  */
    private string $smartyParsedContent = '123';

    public function testDisableSmartyForCmsContentWithProduct(): void
    {
        $utilsView = oxNew(UtilsView::class);
        $utilsView->getRenderedContent($this->smartyUnparsedContent, []);

        $this->assertSame($this->smartyParsedContent, $utilsView->getRenderedContent($this->smartyUnparsedContent, []));
        Registry::getConfig()->setConfigParam('deactivateSmartyForCmsContent', true);
        $this->assertSame($this->smartyUnparsedContent, $utilsView->getRenderedContent($this->smartyUnparsedContent, []));
    }
}
