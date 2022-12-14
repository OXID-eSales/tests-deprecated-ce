<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Application\Utility;

use OxidEsales\EshopCommunity\Internal\Container\BootstrapContainerFactory;
use OxidEsales\EshopCommunity\Internal\Transition\Utility\BasicContextInterface;
use OxidEsales\EshopCommunity\Tests\ContainerTrait;
use PHPUnit\Framework\TestCase;

final class BasicContextTest extends TestCase
{
    use ContainerTrait;

    /**
     * @var BasicContextInterface
     */
    private $basicContext;

    protected function setUp(): void
    {
        $this->basicContext = BootstrapContainerFactory::getBootstrapContainer()->get(BasicContextInterface::class);

        parent::setUp();
    }

    public function testGetConfigFilePath()
    {
        $this->assertFileExists($this->basicContext->getConfigFilePath());
    }

    public function testGetDefaultShopId()
    {
        $this->assertSame(1, $this->basicContext->getDefaultShopId());
    }
}
