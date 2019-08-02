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
use DoctrineExtensions\Query\Mysql\DateFormat;
use DoctrineExtensions\Query\Mysql\IfElse;
use DoctrineExtensions\Query\Mysql\IfNull;
use DoctrineExtensions\Query\Mysql\Regexp;
use DoctrineExtensions\Query\Mysql\Replace;
use Shopware\Components\CacheManager;
use Shopware\Components\ShopwareReleaseStruct;

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
     * @var ShopwareReleaseStruct
     */
    protected $release;

    /**
     * @throws \Exception
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function __construct(array $options, \Zend_Cache_Core $cache, RepositoryFactory $repositoryFactory, ShopwareReleaseStruct $release)
    {
        // Specifies the FQCN of a subclass of the EntityRepository.
        // That will be available for all entities without a custom repository class.
        $this->setDefaultRepositoryClassName('Shopware\Components\Model\ModelRepository');

        $this->setProxyDir($options['proxyDir']);
        $this->setProxyNamespace($options['proxyNamespace']);

        $this->setRepositoryFactory($repositoryFactory);
        $this->setAutoGenerateProxyClasses(AbstractProxyFactory::AUTOGENERATE_FILE_NOT_EXISTS);

        $this->setAttributeDir($options['attributeDir']);

        Type::overrideType('datetime', \Shopware\Components\Model\DBAL\Types\DateTimeStringType::class);
        Type::overrideType('date', \Shopware\Components\Model\DBAL\Types\DateStringType::class);

        $this->addCustomStringFunction('DATE_FORMAT', DateFormat::class);
        $this->addCustomStringFunction('IFNULL', IfNull::class);
        $this->addCustomStringFunction('IF', IfElse::class);
        $this->addCustomStringFunction('RegExp', Regexp::class);
        $this->addCustomStringFunction('Replace', Replace::class);

        $this->release = $release;

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
     * @return Configuration
     */
    public function setCache(CacheProvider $cache)
    {
        // Set namespace for doctrine cache provider to avoid collisions
        $namespace = $this->cacheNamespace !== null ? $this->cacheNamespace : md5(
            $this->getProxyDir() . $this->release->getRevision()
        );
        $cache->setNamespace('dc2_' . $namespace . '_');

        $this->setMetadataCacheImpl($cache);
        $this->setQueryCacheImpl($cache);
        $this->setResultCacheImpl($cache);

        return $this;
    }

    /**
     * @return CacheProvider|null
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

    public function setCacheResource(\Zend_Cache_Core $cacheResource)
    {
        $cache = new Cache($cacheResource, 'Shopware_Models_' . $this->release->getRevision() . '_', [CacheManager::ITEM_TAG_MODELS]);

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
            if (@mkdir($dir, 0777, true) === false && !is_dir($dir)) {
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
            if (@mkdir($dir, 0777, true) === false && !is_dir($dir)) {
                throw new \RuntimeException(sprintf("Unable to create the doctrine proxy directory (%s)\n", $dir));
            }
        } elseif (!is_writable($dir)) {
            throw new \RuntimeException(sprintf("Unable to write in the doctrine proxy directory (%s)\n", $dir));
        }

        parent::setProxyDir($dir);
    }

    /**
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

        if (isset($options['redisAuth'])) {
            $redis->auth($options['redisAuth']);
        }

        $redis->select($options['redisDbIndex']);
        $cache = new RedisCache();
        $cache->setRedis($redis);

        // RedisCache->setRedis might configure igbinary as serializer, which might cause problems
        // this enforces the PHP serializer
        $redis->setOption(\Redis::OPT_SERIALIZER, (string) \Redis::SERIALIZER_PHP);

        return $cache;
    }

    /**
     * @param string $provider
     *
     * @throws \Exception
     */
    private function createDefaultProvider($provider)
    {
        $provider = $provider === 'apc' ? 'apcu' : $provider;

        if (!class_exists($provider, false)) {
            $provider = ucfirst($provider);
            $provider = "Doctrine\\Common\\Cache\\{$provider}Cache";
        }

        if (!class_exists($provider)) {
            throw new \Exception(sprintf('Doctrine cache provider "%s" not found failure.', $provider));
        }

        return new $provider();
    }
}
