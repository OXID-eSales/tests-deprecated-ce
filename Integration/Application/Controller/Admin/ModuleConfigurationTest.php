<?php

/**
 * Copyright © OXID eSales AG. All rights reserved.
 * See LICENSE file for license details.
 */

declare(strict_types=1);

namespace OxidEsales\EshopCommunity\Tests\Integration\Application\Controller\Admin;

use OxidEsales\EshopCommunity\Application\Controller\Admin\ModuleConfiguration as ModuleConfigurationController;
use OxidEsales\EshopCommunity\Internal\Container\ContainerFactory;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\Bridge\ModuleConfigurationDaoBridgeInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Configuration\DataObject\ModuleConfiguration;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Facade\ModuleSettingServiceInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\DataObject\OxidEshopPackage;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Install\Service\ModuleInstallerInterface;
use OxidEsales\EshopCommunity\Internal\Framework\Module\Setup\Bridge\ModuleActivationBridgeInterface;
use OxidEsales\EshopCommunity\Tests\Integration\IntegrationTestCase;
use Psr\Container\ContainerInterface;

/**
 * @internal
 */
final class ModuleConfigurationTest extends IntegrationTestCase
{
    private string $testModuleId = 'testModuleId';

    public function tearDown(): void
    {
        $this->uninstallTestModule();
        parent::tearDown();
    }

    public function testRender(): void
    {
        $this->installTestModule();

        $_POST['oxid'] = $this->testModuleId;

        $moduleConfigurationController = oxNew(ModuleConfigurationController::class);

        $this->assertEquals('module_config', $moduleConfigurationController->render());

        $viewData = $moduleConfigurationController->getViewData();

        $this->assertSame(
            $this->testModuleId,
            $viewData['oModule']->getId()
        );
    }

    public function testSaveConfVarsForInactiveModule(): void
    {
        $this->installTestModule();

        $_POST['oxid'] = $this->testModuleId;
        $_POST['confstrs'] = ['stringSetting' => 'newValue'];

        $moduleConfigurationController = oxNew(ModuleConfigurationController::class);
        $moduleConfigurationController->saveConfVars();

        ContainerFactory::resetContainer();

        $container = ContainerFactory::getInstance()->getContainer();
        $moduleConfiguration = $container->get(ModuleConfigurationDaoBridgeInterface::class)->get($this->testModuleId);

        $this->assertSame(
            'newValue',
            $moduleConfiguration->getModuleSettings()[0]->getValue()
        );
    }

    public function testSaveConfVarsForActiveModule(): void
    {
        $this->installTestModule();
        $this->activateTestModule();

        $_POST['oxid'] = $this->testModuleId;
        $_POST['confstrs'] = ['stringSetting' => 'newValue'];

        $moduleConfigurationController = oxNew(ModuleConfigurationController::class);
        $moduleConfigurationController->saveConfVars();

        ContainerFactory::resetContainer();

        $this->assertSame(
            'newValue',
            $this->getModuleConfiguration()->getModuleSetting('stringSetting')->getValue()
        );
    }

    public function testModuleSettingCacheInvalidatedAfterSave(): void
    {
        $this->installTestModule();
        $this->activateTestModule();

        $oldValueToBeCached = $this->getContainer()->get(ModuleSettingServiceInterface::class)
            ->getString('stringSetting', $this->testModuleId);

        $this->assertEquals('row', $oldValueToBeCached);

        $_POST['oxid'] = $this->testModuleId;
        $_POST['confstrs'] = ['stringSetting' => 'newValue'];

        $moduleConfigurationController = oxNew(ModuleConfigurationController::class);
        $moduleConfigurationController->saveConfVars();

        ContainerFactory::resetContainer();

        $this->assertSame(
            'newValue',
            $this->getContainer()->get(ModuleSettingServiceInterface::class)
                ->getString('stringSetting', $this->testModuleId)->toString()
        );
    }

    public function testSaveConfVarsSavesNumAsInteger(): void
    {
        $this->installTestModule();
        $this->activateTestModule();

        $_POST['oxid'] = $this->testModuleId;
        $_POST['confstrs'] = ['testInt' => '321', 'testFloat' => '123.321'];

        $moduleConfigurationController = oxNew(ModuleConfigurationController::class);
        $moduleConfigurationController->saveConfVars();

        ContainerFactory::resetContainer();

        $this->assertSame(
            321,
            $this->getModuleConfiguration()->getModuleSetting('testInt')->getValue()
        );
        $this->assertSame(
            123.321,
            $this->getModuleConfiguration()->getModuleSetting('testFloat')->getValue()
        );
    }

    private function installTestModule(): void
    {
        $this->getContainer()->get(ModuleInstallerInterface::class)->install(
            new OxidEshopPackage(__DIR__ . '/Fixtures/testModule/')
        );
    }

    private function activateTestModule(): void
    {
        $this->getContainer()->get(ModuleActivationBridgeInterface::class)->activate($this->testModuleId, 1);
    }

    private function getModuleConfiguration(): ModuleConfiguration
    {
        return $this->getContainer()->get(ModuleConfigurationDaoBridgeInterface::class)->get($this->testModuleId);
    }

    private function uninstallTestModule(): void
    {
        $this->getContainer()->get(ModuleInstallerInterface::class)->uninstall(
            new OxidEshopPackage(__DIR__ . '/Fixtures/testModule/')
        );
    }

    private function getContainer(): ContainerInterface
    {
        return ContainerFactory::getInstance()->getContainer();
    }
}
