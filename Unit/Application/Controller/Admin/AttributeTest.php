<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Application\Controller\Admin;

/**
 * Tests for Attribute class
 */
class AttributeTest extends \OxidTestCase
{

    /**
     * Attribute::Render() test case
     */
    public function testRender(): void
    {
        // testing..
        $oView = oxNew('OxidEsales\\Eshop\\Application\\Controller\\Admin\\AttributeController');
        $this->assertEquals('attribute', $oView->render());
    }
}
