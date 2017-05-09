<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
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

use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\CachedReader;
use Doctrine\Common\Annotations\Reader;
use Doctrine\Common\Cache\ApcuCache;
use Doctrine\Common\Cache\CacheProvider;
use Doctrine\Common\Cache\RedisCache;
use Doctrine\Common\Cache\XcacheCache;
use Doctrine\Common\Proxy\AbstractProxyFactory;
use Doctrine\DBAL\Types\Type;
use Doctrine\ORM\Configuration as BaseConfiguration;
use Doctrine\ORM\Repository\RepositoryFactory;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
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
     * Custom namespace for doctrine cache provider
     *
     * @var string
     */
    protected $cacheNamespace = null;

    /**
     * @param array             $options
     * @param \Zend_Cache_Core  $cache
     * @param RepositoryFactory $repositoryFactory
     */
    public function __construct(array $options, \Zend_Cache_Core $cache, RepositoryFactory $repositoryFactory)
    {
        // Specifies the FQCN of a subclass of the EntityRepository.
        // That will be available for all entities without a custom repository class.
        $this->setDefaultRepositoryClassName('Shopware\Components\Model\ModelRepository');

        $this->setProxyDir($options['proxyDir']);
        $this->setProxyNamespace($options['proxyNamespace']);

        $this->setRepositoryFactory($repositoryFactory);
        $this->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS);

        $this->setAttributeDir($options['attributeDir']);

        Type::overrideType('datetime', 'Shopware\Components\Model\DBAL\Types\DateTimeStringType');
        Type::overrideType('date', 'Shopware\Components\Model\DBAL\Types\DateStringType');
        Type::overrideType('array', 'Shopware\Components\Model\DBAL\Types\AllowInvalidArrayType');

        $this->addCustomStringFunction('DATE_FORMAT', 'Shopware\Components\Model\Query\Mysql\DateFormat');
        $this->addCustomStringFunction('IFNULL', 'Shopware\Components\Model\Query\Mysql\IfNull');
        $this->addCustomStringFunction('RegExp', 'Shopware\Components\Model\Query\Mysql\RegExp');
        $this->addCustomStringFunction('Replace', 'Shopware\Components\Model\Query\Mysql\Replace');

        // Load custom namespace for doctrine cache provider, if provided
        if (isset($options['cacheNamespace'])) {
            $this->cacheNamespace = $options['cacheNamespace'];
        }

        if (isset($options['cacheProvider'])) {
            $this->setCacheProvider($options);
        }

        if ($this->getMetadataCacheImpl() === null) {
            $this->setCacheResource($cache);
        }
    }

    /**
     * @param CacheProvider $cache
     *
     * @return Configuration
     */
    public function setCache(CacheProvider $cache)
    {
        // Set namespace for doctrine cache provider to avoid collisions
        $namespace = !is_null($this->cacheNamespace) ? $this->cacheNamespace : md5(
            $this->getProxyDir() . \Shopware::REVISION
        );
        $cache->setNamespace('dc2_' . $namespace . '_');

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

        if (extension_loaded('apcu')) {
            $cache = new ApcuCache();
        } elseif (extension_loaded('xcache')) {
            $cache = new XcacheCache();
        }

        return $cache;
    }

    /**
     * @param array $options
     *
     * @throws \Exception
     */
    public function setCacheProvider(array $options)
    {
        $provider = $options['cacheProvider'];

        $cache = null;

        switch (strtolower($provider)) {
            case 'auto':
                $cache = $this->detectCacheProvider();
                break;
            case 'redis':
                $cache = $this->createRedisCacheProvider($options);
                break;
            default:
                $cache = $this->createDefaultProvider($provider);
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
        $cache = new Cache($cacheResource, null, ['Shopware_Models']);

        $this->setCache($cache);
    }

    /**
     * @return Reader
     */
    public function getAnnotationsReader()
    {
        $reader = new AnnotationReader();
        $cache = $this->getMetadataCacheImpl();

        $reader = new CachedReader(
            $reader,
            $cache
        );

        return $reader;
    }

    /**
     * @param string $dir
     *
     * @throws \RuntimeException
     *
     * @return Configuration
     */
    public function setAttributeDir($dir)
    {
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf("Unable to create the doctrine attribute directory (%s)\n", $dir));
            }
        } elseif (!is_writable($dir)) {
            throw new \RuntimeException(sprintf("Unable to write in the doctrine attribute directory (%s)\n", $dir));
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
     * Sets the directory where Doctrine generates any necessary proxy class files.
     *
     * @param string $dir
     *
     * @throws \RuntimeException
     */
    public function setProxyDir($dir)
    {
        if (!is_dir($dir)) {
            if (false === @mkdir($dir, 0777, true) && !is_dir($dir)) {
                throw new \RuntimeException(sprintf("Unable to create the doctrine proxy directory (%s)\n", $dir));
            }
        } elseif (!is_writable($dir)) {
            throw new \RuntimeException(sprintf("Unable to write in the doctrine proxy directory (%s)\n", $dir));
        }

        parent::setProxyDir($dir);
    }

    /**
     * @param array $options
     *
     * @return RedisCache
     */
    private function createRedisCacheProvider(array $options)
    {
        $redis = new \Redis();
        if (isset($options['redisPersistent']) && $options['redisPersistent'] == true) {
            $redis->pconnect($options['redisHost'], $options['redisPort']);
        } else {
            $redis->connect($options['redisHost'], $options['redisPort']);
        }
        $redis->select($options['redisDbIndex']);
        $cache = new RedisCache();
        $cache->setRedis($redis);

        // RedisCache->setRedis might configure igbinary as serializer, which might cause problems
        // this enforces the PHP serializer
        $redis->setOption(\Redis::OPT_SERIALIZER, \Redis::SERIALIZER_PHP);

        return $cache;
    }

    /**
     * @param $provider
     *
     * @throws \Exception
     *
     * @return mixed
     */
    private function createDefaultProvider($provider)
    {
        $provider = $provider === 'apc' ? 'apcu' : $provider;

        if (!class_exists($provider, false)) {
            $provider = ucfirst($provider);
            $provider = "Doctrine\\Common\\Cache\\{$provider}Cache";
        }

        if (!class_exists($provider)) {
            throw new \Exception('Doctrine cache provider "' . $provider . "' not found failure.");
        }

        return new $provider();
    }
}
