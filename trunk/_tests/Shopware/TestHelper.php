<?php
/**
 * Shopware
 *
 * LICENSE
 *
 * Available through the world-wide-web at this URL:
 * http://shopware.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     $Author$
 */

$docPath = realpath(dirname(__FILE__) . '/../../');

set_include_path(
    get_include_path() . PATH_SEPARATOR .
    $docPath . '/engine/Library/' . PATH_SEPARATOR .   // Library
    $docPath . '/engine/' . PATH_SEPARATOR .           // Shopware
    $docPath . '/templates/' . PATH_SEPARATOR .        // Templates
    $docPath
);

include_once 'Enlight/Application.php';
include_once 'Shopware/Application.php';

/**
 * Shopware Test Helper
 *
 * {@inheritdoc }
 *
 * @category   Shopware
 * @package    Shopware_Tests
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
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
    public function __construct()
    {
        $this->testPath = __DIR__ . $this->DS();
        $this->oldPath = realpath(__DIR__ . '/../../') . $this->DS();

        parent::__construct('testing', $this->TestPath() . 'Configs/Default.php');

        $this->Bootstrap()->loadResource('Zend');
        $this->Bootstrap()->loadResource('Cache');
        $this->Bootstrap()->loadResource('Db');
        $this->Bootstrap()->loadResource('Table');
        $this->Bootstrap()->loadResource('Plugins');
        //$this->Bootstrap()->loadResource('Session');

        $this->Bootstrap()->Plugins()->Core()
             ->ErrorHandler()->registerErrorHandler(E_ALL | E_STRICT);

        $this->Loader()->loadClass('Shopware_Components_Test_TicketListener');
        $this->Loader()->loadClass('Shopware_Components_Test_MailListener');

        $repository = $this->Bootstrap()->Models();
        /** @var $repository \Shopware\Models\Shop\Repository */
        $repository = $repository->getRepository('Shopware\Models\Shop\Shop');
        $shop = $repository->getActiveDefault();
        $shop->registerResources($this->Bootstrap());

        Enlight_Components_Test_Selenium_TestCase::setDefaultBrowserUrl(
            'http://' . $shop->getHost() . $shop->getBasePath() . '/'
        );
        $_SERVER['HTTP_HOST'] = $shop->getHost();

        Shopware()->Models()->getRepository(
            'Shopware\Models\Category\Category'
        )->recover();
    }

    /**
     * Returns the path to test directory.
     *
     * @param string $path
     * @return string
     */
    public function TestPath($path = null)
    {
        if($path !== null) {
            $path = str_replace('_', $this->DS(), $path);
            return $this->testPath . $path . $this->DS();
        }
        return $this->testPath;
    }

    /**
     * Returns the singleton instance of the tests helper.
     *
     * @return TestHelper
     */
    public static function Instance()
    {
        if(!isset(self::$instance)) {
            self::$instance = new self();
        }
        return self::$instance;
    }
}

/**
 * Start test application
 */
TestHelper::Instance();
