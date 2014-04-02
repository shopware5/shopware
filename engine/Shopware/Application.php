<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

use Shopware\Components\DependencyInjection\Container;

/**
 * Shopware Application
 *
 * @category  Shopware
 * @package   Shopware
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class Shopware extends Enlight_Application
{
    const VERSION      = '___VERSION___';
    const VERSION_TEXT = '___VERSION_TEXT___';
    const REVISION     = '___REVISION___';

    protected $app     = 'Shopware';
    protected $appPath = 'engine/Shopware/';
    protected $oldPath = null;

    /**
     * @var Container
     */
    protected $container;

    /**
     * Constructor method
     *
     * @param string $environment
     * @param array $options
     * @param Container $container
     */
    public function __construct($environment, array $options, Container $container)
    {
        $this->container = $container;

        Shopware($this);

        if ($this->oldPath === null) {
            $this->oldPath = realpath(__DIR__ . '/../../') . DIRECTORY_SEPARATOR;
        }

        parent::__construct($environment, $options, $container);

        $container->setBootstrap($this->Bootstrap());
        $container->setApplication($this);
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
     * @return Container
     */
    public function Container()
    {
        return $this->container;
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
     * Returns the instance of the plugin manager, which is initialed in the class constructor
     *
     * @return Enlight_Plugin_PluginManager
     */
    public function Plugins()
    {
        return $this->_bootstrap->getResource('plugin_manager');
    }

    /**
     * Returns the instance of the snippet manager
     *
     * @return Shopware_Components_Snippet_Manager
     */
    public function Snippets()
    {
        return $this->_bootstrap->getResource('snippets');
    }

    /**
     * Returns the instance of the password manager
     *
     * @return \Shopware\Components\Password\Manager
     */
    public function PasswordEncoder()
    {
        return $this->_bootstrap->getResource('PasswordEncoder');
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
