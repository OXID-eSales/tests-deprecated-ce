<?php

/**
 *
 * @category      module
 * @package       moduleone
 * @author        John Doe
 * @link          www.johndoe.com
 * @copyright (C) John Doe 20162016
 */

/**
 * Metadata version
 */
$sMetadataVersion = '2.1';

/**
 * Module information
 */
$aModule = array(
    'id'          => 'unifiednamespace_module2',
    'title'       => array(
        'de' => 'OXID eSales example module2',
        'en' => 'OXID eSales example module2',
    ),
    'description' => array(
        'de' => 'This module overrides ContentController::getTitle()',
        'en' => 'This module overrides ContentController::getTitle()',
    ),
    'version'     => '1.0.0',
    'author'      => 'John Doe',
    'url'         => 'www.johndoe.com',
    'email'       => 'john@doe.com',
    'extend'      => array(
        'content' => \OxidEsales\EshopCommunity\Tests\Integration\Modules\TestData\modules\unifiednamespace_module2\Controller\Test2ContentController::class,
        'test2content' => \OxidEsales\EshopCommunity\Tests\Integration\Modules\TestData\modules\unifiednamespace_module2\Model\Test2Content::class,
    ),
    'templates'   => array(),
    'settings'    => array(),
    'events'      => array(),
);
