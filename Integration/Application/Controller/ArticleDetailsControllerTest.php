<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Application\Controller;

use OxidEsales\Eshop\Application\Controller\ArticleDetailsController;
use OxidEsales\Eshop\Application\Model\ArticleList;
use OxidEsales\Eshop\Core\Config;
use OxidEsales\Eshop\Core\Registry;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\TestingLibrary\UnitTestCase;

final class ArticleDetailsControllerTest extends UnitTestCase
{
    private string $smartyUnparsedContent = '[{1|cat:2|cat:3}]';
    private string $smartyParsedContent = '123';
    private ArticleDetailsController $controller;

    protected function setUp(): void
    {
        parent::setUp();
        $this->prepareTestData();
    }

    public function testGetParsedContent(): void
    {
        ContainerFactory::resetContainer();
        $parsedContent = $this->controller->getMetaDescription();

        $this->assertStringEndsWith($this->smartyParsedContent, $parsedContent);
    }

    public function testGetParsedContentWithConfigurationOff(): void
    {
        ContainerFactory::resetContainer();
        $config = Registry::getConfig();
        $config->setConfigParam('deactivateSmartyForCmsContent', true);

        $controller = oxNew(ArticleDetailsController::class);
        $parsedContent = $controller->getMetaDescription();

        $this->assertStringEndsWith($this->smartyUnparsedContent, $parsedContent);
    }

    private function prepareTestData(): void
    {
        $productList = oxNew(ArticleList::class);

        $testProductId = $productList->getList()->arrayKeys()[0];
        $product = $productList->getList()[$testProductId];
        $product->setArticleLongDesc($this->smartyUnparsedContent);
        $product->save();

        $_GET['anid'] = $testProductId;
        $this->controller = oxNew(ArticleDetailsController::class);
    }
}
