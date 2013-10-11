<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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
 *
 * @category   Shopware
 * @package    Shopware
 * @subpackage Shopware
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     $Author$
 */

use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Shopware Application
 */
class Shopware extends Enlight_Application
{
    const VERSION      = '___VERSION___';
    const VERSION_TEXT = '___VERSION_TEXT___';
    const REVISION     = '___REVISION___';

    protected $app     = 'Shopware';
    protected $appPath = 'engine/Shopware/';
    protected $oldPath = null;
    protected $resourceLoader;

    /**
     * Constructor method
     *
     * @param string $environment
     * @param mixed $options
     */
    public function __construct($environment = 'production', array $options, \Enlight_Components_ResourceLoader $resourceLoader)
    {
        $this->resourceLoader = $resourceLoader;

        Shopware($this);

        if ($this->oldPath === null) {
            $this->oldPath = dirname(realpath(dirname($this->AppPath()))) . $this->DS();
        }


        error_reporting(-1);
        ini_set('display_errors', true);

        parent::__construct($environment, $options);

        //$this->resourceLoader->setBootstrap($this->Bootstrap());
        //$this->resourceLoader->setEventManager($this->Events());
    }

    /**
     * Returns old path
     *
     * @param string $path
     * @return string
     */
    public function OldPath($path = null)
    {
        if ($path !== null) {
            $path = str_replace('_', $this->DS(), $path);
            return $this->oldPath . $path . $this->DS();
        }
        return $this->oldPath;
    }

    /**
     * Returns document path
     *
     * @param string $path
     * @return string
     */
    public function DocPath($path = null)
    {
        return $this->OldPath($path);
    }

    /**
     * Returns injection container
     *
     * @return \Enlight_Components_ResourceLoader
     */
    public function ResourceLoader()
    {
        return $this->resourceLoader;
    }

    /**
     * Returns front controller instance
     *
     * @return Enlight_Controller_Front
     */
    public function Front()
    {
        return $this->Bootstrap()->getResource('Front');
    }

    /**
     * Returns template instance
     *
     * @return Enlight_Template_Manager
     */
    public function Template()
    {
        return $this->_bootstrap->getResource('Template');
    }

    /**
     * Returns config instance
     *
     * @return Shopware_Components_Config
     */
    public function Config()
    {
        return $this->_bootstrap->getResource('Config');
    }

    /**
     * Returns access layer to deprecated shopware frontend objects
     *
     * @return Shopware_Components_Modules
     */
    public function Modules()
    {
        return $this->_bootstrap->getResource('Modules');
    }

    /**
     * Returns config instance
     *
     * @return \Shopware\Models\Shop\Shop
     */
    public function Shop()
    {
        return $this->_bootstrap->getResource('Shop');
    }

    /**
     * Returns database instance
     *
     * @return Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public function Db()
    {
        return $this->_bootstrap->getResource('Db');
    }

    /**
     * Returns doctrine instance
     *
     * @return Shopware\Components\Model\ModelManager
     */
    public function Models()
    {
        return $this->_bootstrap->getResource('Models');
    }

    /**
     * Returns session instance
     *
     * @return Enlight_Components_Session_Namespace
     */
    public function Session()
    {
        return $this->_bootstrap->getResource('Session');
    }

    /**
     * Returns session instance
     *
     * @return Shopware_Components_Acl
     */
    public function Acl()
    {
        return $this->_bootstrap->getResource('Acl');
    }

    /**
     * Returns session instance
     *
     * @return Shopware_Components_TemplateMail
     */
    public function TemplateMail()
    {
        return $this->_bootstrap->getResource('TemplateMail');
    }

    /**
     * Returns application instance
     *
     * @return Shopware
     */
    public static function Instance()
    {
        return self::$instance;
    }
}

/**
 * Returns application instance
 *
 * @param   Enlight_Application $newInstance
 * @return  Enlight_Application
 */
function Enlight($newInstance = null)
{
    static $instance;
    if (isset($newInstance)) {
        $oldInstance = $instance;
        $instance    = $newInstance;
        return $oldInstance;
    } elseif (!isset($instance)) {
        $instance = Enlight_Application::Instance();
    }
    return $instance;
}

/**
 * Returns application instance
 *
 * @param   Shopware $newInstance
 * @return  Shopware
 */
function Shopware($newInstance = null)
{
    static $instance;
    if (isset($newInstance)) {
        $oldInstance = $instance;
        $instance    = $newInstance;
        return $oldInstance;
    } elseif (!isset($instance)) {
        $instance = Enlight_Application::Instance();
    }
    return $instance;
}
