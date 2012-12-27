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
     * @var string
     */
    protected $attributeDir;

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
        $this->setAutoGenerateProxyClasses(!empty($options['autoGenerateProxyClasses']));
        $this->setAttributeDir($options['attributeDir']);

        $this->addEntityNamespace('Shopware', 'Shopware\Models');
        $this->addEntityNamespace('Custom', 'Shopware\CustomModels');

        $this->addCustomStringFunction('DATE_FORMAT', 'DoctrineExtensions\Query\Mysql\DateFormat');
        $this->addCustomStringFunction('IFNULL', 'DoctrineExtensions\Query\Mysql\IfNull');

        if(isset($options['cacheProvider'])) {
            $this->setCacheProvider($options['cacheProvider']);
        }
    }

    public function setCacheProvider($provider)
    {
        if(!class_exists($provider, false)) {
            $provider = "Doctrine\\Common\\Cache\\{$provider}Cache";
        }
        if(!class_exists($provider)) {
            throw new \Exception('Doctrine cache provider "' . $provider. "' not found failure.");
        }
        $cache = new $provider();
        $this->setMetadataCacheImpl($cache);
        $this->setQueryCacheImpl($cache);
    }

    /**
     * @param \Zend_Cache_Core $cacheResource
     */
    public function setCacheResource(\Zend_Cache_Core $cacheResource)
    {
        // Check if native Doctrine ApcCache may be used
        if ($cacheResource->getBackend() instanceof \Zend_Cache_Backend_Apc) {
            $cache = new \Doctrine\Common\Cache\ApcCache();
        } else {
            $cache = new Cache($cacheResource);
        }

        $this->setMetadataCacheImpl($cache);
        $this->setQueryCacheImpl($cache);
    }

    /**
     * @return \Doctrine\Common\Annotations\AnnotationReader
     */
    public function getAnnotationsReader()
    {
        $reader = new \Doctrine\Common\Annotations\AnnotationReader;
        $cache = $this->getMetadataCacheImpl();
        if ($this->getMetadataCacheImpl() instanceof Cache) {
            $reader = new \Doctrine\Common\Annotations\FileCacheReader(
                $reader,
                $this->getProxyDir()
            );
        } else {
            $reader = new \Doctrine\Common\Annotations\CachedReader(
                $reader,
                $cache
            );
        }
        return $reader;
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
     * @param string $attributeDir
     */
    public function setAttributeDir($attributeDir)
    {
        $this->attributeDir = $attributeDir;
    }

    /**
     * @return string
     */
    public function getAttributeDir()
    {
        return $this->attributeDir;
    }
}
