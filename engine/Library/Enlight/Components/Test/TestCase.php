<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

use PHPUnit\Framework\TestCase;

/**
 * Basic class for each specified test case.
 *
 * The Enlight_Components_Test_TestCase is the basic class for all specified test cases.
 * The enlight test case basic class extends PHPUnit\Framework\TestCase and sets the database link automatically.
 */
abstract class Enlight_Components_Test_TestCase extends TestCase
{
    /**
     * Sets up the fixture, for example, open a network connection.
     */
    protected function setUp(): void
    {
        parent::setUp();

        // Clear entitymanager to prevent weird 'model shop not persisted' errors.
        Shopware()->Models()->clear();
    }

    /**
     * Performs operation returned by getSetUpOperation().
     */
    protected function tearDown(): void
    {
        set_time_limit(0);
        ini_restore('memory_limit');
    }

    /**
     * Allows to set a Shopware config
     *
     * @param bool|float|int|string|null $value
     */
    protected function setConfig(string $name, $value): void
    {
        Shopware()->Container()->get('config_writer')->save($name, $value);
        Shopware()->Container()->get(Zend_Cache_Core::class)->clean();
        Shopware()->Container()->get(Shopware_Components_Config::class)->setShop(Shopware()->Shop());
    }
}
