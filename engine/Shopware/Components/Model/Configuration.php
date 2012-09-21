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
 * @package    Shopware_Components_Model
 * @subpackage Model
 * @copyright  Copyright (c) 2012, shopware AG (http://www.shopware.de)
 * @version    $Id$
 * @author     Heiner Lohaus
 * @author     $Author$
 */

namespace Shopware\Components\Model;
use \Doctrine\ORM\Configuration as BaseConfiguration;

/**
 *
 */
class Configuration extends BaseConfiguration
{
    /**
     * @var
     */
    protected $attributeDir;

    /**
     * @var \Doctrine\Common\Cache\Cache
     */
    protected $cache;

    /**
     * @param \Doctrine\Common\Cache\Cache $cache
     */
    public function setCache(\Doctrine\Common\Cache\Cache $cache)
    {
        $this->cache = $cache;
    }

    /**
     * @return \Doctrine\Common\Cache\Cache
     */
    public function getCache()
    {
        if ($this->cache === null) {
            if (extension_loaded('apc')) {
                $cache = new \Doctrine\Common\Cache\ApcCache;
            } else if (extension_loaded('xcache')) {
                $cache = new \Doctrine\Common\Cache\XcacheCache;
            } else if (extension_loaded('memcache')) {
                $memcache = new \Memcache();
                $memcache->connect('127.0.0.1');
                if ($memcache->connect('127.0.0.1') !== false) {
                    $cache = new \Doctrine\Common\Cache\MemcacheCache();
                    $cache->setMemcache($memcache);
                } else {
                    $cache = new ArrayCache;
                }
            } else {
                $cache = new ArrayCache;
            }

            $cache->setNamespace("dc2_" . md5($this->getProxyDir()) . "_"); // to avoid collisions

            $this->cache = $cache;
        }

        return $this->cache;
    }


    /**
     * @param array $attributes
     */
    public function setAttributes($attributes)
    {
        $this->_attributes = $attributes;
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return $this->_attributes;
    }

    /**
     * @param $options
     */
    public function __construct($options)
    {
        // Specifies the FQCN of a subclass of the EntityRepository.
        // That will be available for all entities without a custom repository class.
        $this->setDefaultRepositoryClassName('Shopware\Components\Model\ModelRepository');

        // set the proxy dir and set some options
        $proxyDir = Shopware()->Loader()->isReadable($options['proxyDir']);
        $proxyDir = realpath($proxyDir);
        $this->setProxyDir($proxyDir);
        $this->setProxyNamespace($options['proxyNamespace']);

        if (isset($options['autoGenerateProxyClasses']) && $options['autoGenerateProxyClasses']) {
            $this->setAutoGenerateProxyClasses(true);
        } else {
            $this->setAutoGenerateProxyClasses(false);
        }

        $this->setAttributeDir($options['attributeDir']);

        $this->addEntityNamespace('Shopware', 'Shopware\Models');
        $this->addEntityNamespace('Custom', 'Shopware\CustomModels');

        $this->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');
        $this->addCustomStringFunction('IFNULL', 'DoctrineExtensions\Query\Mysql\IfNull');
    }

    /**
     * @param $cacheResource
     */
    public function setCacheResource($cacheResource)
    {
        $cache = $this->getCache();

        $this->setMetadataCacheImpl($cache);
        $this->setQueryCacheImpl($cache);
    }

    /**
     * @param null $hookManager
     */
    public function setHookManager($hookManager = null)
    {
        $this->_attributes['hookManager'] = $hookManager;
    }

    /**
     * @return null
     */
    public function getHookManager()
    {
        return isset($this->_attributes['hookManager']) ?
            $this->_attributes['hookManager'] : null;
    }

    /**
     * @param $attributeDir
     */
    public function setAttributeDir($attributeDir)
    {
        $this->attributeDir = $attributeDir;
    }

    /**
     * @return mixed
     */
    public function getAttributeDir()
    {
        return $this->attributeDir;
    }
}
