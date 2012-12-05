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
 * @package    Shopware_Doctrine
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 * @version    $Id$
 * @author     Benjamin Cremer
 */

$docPath = realpath(dirname(__FILE__) . '/../');

set_include_path(
    $docPath . '/engine/Library/' . PATH_SEPARATOR .   // Library
    $docPath . '/engine/' . PATH_SEPARATOR .           // Shopware
    $docPath
);

include_once 'Enlight/Application.php';
include_once 'Shopware/Application.php';

/**
 * Shopware Doctrine Helper
 *
 * @category   Shopware
 * @package    Shopware_Doctrine
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 */
class DoctrineHelper extends Shopware
{
    /**
     * Constructor method
     *
     * Loads all needed resources for the test.
     */
    public function __construct()
    {
        $this->oldPath = realpath(__DIR__ . '/../') . '/';

        parent::__construct();

        $this->Bootstrap()->loadResource('Zend');
        $this->Bootstrap()->Application()
                          ->Loader()
                          ->registerNamespace('Symfony\\Component\\Console', 'Doctrine/Symfony/Component/Console/');
    }
}

$helper = new DoctrineHelper();

$em = $helper->Models();

$helperSet = new \Symfony\Component\Console\Helper\HelperSet(array(
    'db' => new \Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper($em->getConnection()),
    'em' => new \Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper($em)
));

\Doctrine\ORM\Tools\Console\ConsoleRunner::run($helperSet);
