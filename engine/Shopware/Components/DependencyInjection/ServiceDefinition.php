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

namespace Shopware\Components\DependencyInjection;

/**
 * @category  Shopware
 * @package   Shopware\Components\DependencyInjection
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class ServiceDefinition
{
    /**
     * @var String
     */
    protected $xmlPath;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var string
     */
    protected $alias;

    /**
     * @param       $xmlPath
     * @param       $alias
     * @param array $config
     *
     * @throws \Exception
     */
    public function __construct($xmlPath, $alias = null, array $config = null)
    {
        if (!empty($config) && empty($alias)) {
            throw new \Exception('The passed service configuration requires a configuration alias.');
        }
        $this->alias = $alias;
        $this->config = $config;
        $this->xmlPath = $xmlPath;
    }

    /**
     * @param string $alias
     */
    public function setAlias($alias)
    {
        $this->alias = $alias;
    }

    /**
     * @return string
     */
    public function getAlias()
    {
        return $this->alias;
    }

    /**
     * @param array $config
     */
    public function setConfig($config)
    {
        $this->config = $config;
    }

    /**
     * @return array
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * @param String $xmlPath
     */
    public function setXmlPath($xmlPath)
    {
        $this->xmlPath = $xmlPath;
    }

    /**
     * @return String
     */
    public function getXmlPath()
    {
        return $this->xmlPath;
    }
}
