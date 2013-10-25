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

namespace Shopware\Components\Model;

use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration as BaseConfiguration;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\FileCacheReader;
use Doctrine\Common\Cache\ApcCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\XcacheCache;

/**
 * @category  Shopware
 * @package   Shopware\Components\Model
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Configuration extends BaseConfiguration
{
    /**
     * Directory for generated attribute models
     *
     * @var string
     */
    protected $attributeDir;

    /**
     * Directory for cached anotations
     *
     * @var string
     */
    protected $fileCacheDir;

    /**
     * @param array $options
     * @param \Zend_Cache_Core $cache
     * @param \Enlight_Hook_HookManager $hookManager
     */
    public function __construct($options, \Zend_Cache_Core $cache, \Enlight_Hook_HookManager $hookManager)
    {
        $this->setHookManager($hookManager);

        // Specifies the FQCN of a subclass of the EntityRepository.
        // That will be available for all entities without a custom repository class.
        $this->setDefaultRepositoryClassName('Shopware\Components\Model\ModelRepository');

        $this->setProxyDir($options['proxyDir']);
        $this->setProxyNamespace($options['proxyNamespace']);
        $this->setAutoGenerateProxyClasses(!empty($options['autoGenerateProxyClasses']));

        $this->setAttributeDir($options['attributeDir']);
        $this->setFileCacheDir($options['fileCacheDir']);

        $this->addEntityNamespace('Shopware', 'Shopware\Models');
        $this->addEntityNamespace('Custom', 'Shopware\CustomModels');

        Type::overrideType('datetime', 'Shopware\Components\Model\DBAL\Types\DateTimeStringType');
        Type::overrideType('date', 'Shopware\Components\Model\DBAL\Types\DateStringType');
        Type::overrideType('array', 'Shopware\Components\Model\DBAL\Types\AllowInvalidArrayType');

        $this->addCustomStringFunction('DATE_FORMAT', 'Shopware\Components\Model\Query\Mysql\DateFormat');
        $this->addCustomStringFunction('IFNULL', 'Shopware\Components\Model\Query\Mysql\IfNull');

        if (isset($options['cacheProvider'])) {
            $this->setCacheProvider($options['cacheProvider']);
        }

        if ($this->getMetadataCacheImpl() === null) {
            $this->setCacheResource($cache);
        }
    }

    /**
     * @param  CacheProvider $cache
     * @return Configuration
     */
    public function setCache(CacheProvider $cache)
    {
        $cache->setNamespace("dc2_" . md5($this->getProxyDir() . \Shopware::REVISION) . "_"); // to avoid collisions

        $this->setMetadataCacheImpl($cache);
        $this->setQueryCacheImpl($cache);
        $this->setResultCacheImpl($cache);

        return $this;
    }

    /**
     * @return null|CacheProvider
     */
    public function detectCacheProvider()
    {
        $cache = null;

        if (extension_loaded('apc') && version_compare(phpversion('apc'), '3.1.13', '>=')) {
            $cache = new ApcCache();
        } elseif (extension_loaded('xcache')) {
            $cache = new XcacheCache();
        }

        return $cache;
    }

    /**
     * @param string $provider
     * @throws \Exception
     */
    public function setCacheProvider($provider)
    {
        $cache = null;

        if (strtolower($provider) === 'auto') {
            $cache = $this->detectCacheProvider();
        } else {
            if (!class_exists($provider, false)) {
                $provider = ucfirst($provider);
                $provider = "Doctrine\\Common\\Cache\\{$provider}Cache";
            }
            if (!class_exists($provider)) {
                throw new \Exception('Doctrine cache provider "' . $provider. "' not found failure.");
            }

            $cache = new $provider();
        }

        if ($cache instanceof CacheProvider) {
            $this->setCache($cache);
        }
    }

    /**
     * @param \Zend_Cache_Core $cacheResource
     */
    public function setCacheResource(\Zend_Cache_Core $cacheResource)
    {
        $cache = new Cache($cacheResource);

        $this->setCache($cache);
    }

    /**
     * @return AnnotationReader
     */
    public function getAnnotationsReader()
    {
        $reader = new AnnotationReader;
        $cache = $this->getMetadataCacheImpl();
        if ($this->getMetadataCacheImpl() instanceof Cache) {
            $reader = new FileCacheReader(
                $reader,
                $this->getFileCacheDir()
            );
        } else {
            $reader = new CachedReader(
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
     * @param string $dir
     * @throws \InvalidArgumentException
     * @return Configuration
     */
    public function setAttributeDir($dir)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }

        if (!is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $dir));
        }

        $dir = rtrim(realpath($dir), '\\/') . DIRECTORY_SEPARATOR;

        $this->attributeDir = $dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getAttributeDir()
    {
        return $this->attributeDir;
    }

    /**
     * @param string $dir
     * @throws \InvalidArgumentException
     * @return Configuration
     */
    public function setFileCacheDir($dir)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }

        if (!is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $dir));
        }

        $dir = rtrim(realpath($dir), '\\/') . DIRECTORY_SEPARATOR;

        $this->fileCacheDir = $dir;

        return $this;
    }

    /**
     * @return string
     */
    public function getFileCacheDir()
    {
        return $this->fileCacheDir;
    }

    /**
     * Sets the directory where Doctrine generates any necessary proxy class files.
     *
     * @param string $dir
     * @throws \InvalidArgumentException
     */
    public function setProxyDir($dir)
    {
        if (!is_dir($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" does not exist.', $dir));
        }

        if (!is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $dir));
        }

        $dir = rtrim(realpath($dir), '\\/') . DIRECTORY_SEPARATOR;

        $dir = $dir . '/' . \Shopware::REVISION;
        if (!is_dir($dir)) {
            mkdir($dir, 0775);
        }

        if (!is_writable($dir)) {
            throw new \InvalidArgumentException(sprintf('The directory "%s" is not writable.', $dir));
        }

        parent::setProxyDir($dir);
    }
}
