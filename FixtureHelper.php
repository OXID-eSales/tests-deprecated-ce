<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests;

use OxidEsales\Facts\Facts;
use Symfony\Component\Filesystem\Filesystem;

class FixtureHelper
{
    public static function copyLegacyFixturesToTheShopsOutDirectory(): void
    {
        (new Filesystem())
            ->mirror(
                __DIR__ . '/Fixtures/out',
                (new Facts())->getOutPath()
            );
    }
}
