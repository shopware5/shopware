<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

$docPath = realpath(dirname(__FILE__) . '/../../');

set_include_path(
    get_include_path() . PATH_SEPARATOR .
    $docPath . '/engine/Library/' . PATH_SEPARATOR .   // Library
    $docPath . '/engine/' . PATH_SEPARATOR .           // Shopware
    $docPath . '/templates/' . PATH_SEPARATOR .        // Templates
    $docPath
);

require 'vendor/autoload.php';

include_once 'Enlight/Application.php';
include_once 'Shopware/Application.php';
include_once 'Shopware/Kernel.php';
include_once 'Shopware/ConfigLoader.php';

/**
 * Shopware Test Helper
 *
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class TestHelper extends Shopware
{
    /**
     * The test path
     *
     * @var string
     */
    protected $testPath;

    /**
     * Constructor method
     *
     * Loads all needed resources for the test.
     */
    public function __construct($env, $config, $container)
    {
        $this->testPath = __DIR__ . '/';
        $this->oldPath = realpath(__DIR__ . '/../../') . '/';

        parent::__construct('testing', $config, $container);

        $this->Bootstrap()->loadResource('Zend');
        $this->Bootstrap()->loadResource('Cache');
        $this->Bootstrap()->loadResource('Db');
        $this->Bootstrap()->loadResource('Table');
        $this->Bootstrap()->loadResource('Plugins');

        $this->Bootstrap()->Models()->generateAttributeModels();

        $this->Bootstrap()->Plugins()->Core()->ErrorHandler()->registerErrorHandler(E_ALL | E_STRICT);

        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = $this->Bootstrap()->Models()->getRepository('Shopware\Models\Shop\Shop');
        $shop = $repository->getActiveDefault();
        $shop->registerResources($this->Bootstrap());

        $_SERVER['HTTP_HOST'] = $shop->getHost();
    }

    /**
     * Returns the path to test directory.
     *
     * @param string $path
     * @return string
     */
    public function TestPath($path = null)
    {
        if ($path !== null) {
            $path = str_replace('_', '/', $path);
            return $this->testPath . $path . '/';
        }

        return $this->testPath;
    }
}

class TestKernel extends \Shopware\Kernel
{
    protected function initializeShopware()
    {
        $this->shopware = new \TestHelper(
            $this->environment,
            $this->getConfig(),
            $this->getContainer()
        );
    }

    protected function getConfigPath()
    {
        return __DIR__ . '/Configs/Default.php';
    }

    /**
     * Static method to start boot kernel without leaving local scope in test helper
     */
    public static function start()
    {
        $kernel = new self('testing', true);
        $kernel->boot();
    }
}

TestKernel::start();
