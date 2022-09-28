<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

namespace OxidEsales\EshopCommunity\Tests\Unit\Application\Controller\Admin;

use OxidEsales\Eshop\Application\Controller\Admin\SystemRequirementsMain;
use OxidEsales\EshopCommunity\Internal\Framework\Smarty\SystemRequirements\MissingTemplateBlocksCheckerInterface;
use \oxTestModules;
use Psr\Container\ContainerInterface;

/**
 * Tests for sysreq_main class
 */
class SysreqmainTest extends \OxidTestCase
{

    /**
     * sysreq_main::Render() test case
     *
     * @return null
     */
    public function testRender()
    {
        // testing..
        $oView = oxNew('sysreq_main');
        $this->assertEquals('sysreq_main', $oView->render());
    }

    /**
     * sysreq_main::GetModuleClass() test case
     *
     * @return null
     */
    public function testGetModuleClass()
    {
        // defining parameters
        $oView = oxNew('sysreq_main');
        $this->assertEquals('pass', $oView->getModuleClass(2));
        $this->assertEquals('pmin', $oView->getModuleClass(1));
        $this->assertEquals('null', $oView->getModuleClass(-1));
        $this->assertEquals('fail', $oView->getModuleClass(0));
    }

    /**
     * base test
     *
     * @return null
     */
    public function testGetMissingTemplateBlocks()
    {
        $missingTemplateBlocksChecker = $this->getMockBuilder(MissingTemplateBlocksCheckerInterface::class)
            ->getMock();
        $missingTemplateBlocksChecker->expects($this->any())
            ->method('collectMissingTemplateBlockExtensions')
            ->will($this->returnValue(['someArray']));

        $container = $this->getMockBuilder(ContainerInterface::class)
            ->getMock();
        $container->expects($this->any())
            ->method('get')
            ->with($this->equalTo(MissingTemplateBlocksCheckerInterface::class))
            ->will($this->returnValue($missingTemplateBlocksChecker));
        $systemRequirementsMain = $this->getMockBuilder(SystemRequirementsMain::class)
            ->setMethods(['getContainer'])
            ->getMock();
        $systemRequirementsMain->expects($this->any())
            ->method('getContainer')
            ->will($this->returnValue($container));

        $this->assertEquals(['someArray'], $systemRequirementsMain->getMissingTemplateBlocks());
    }
}
