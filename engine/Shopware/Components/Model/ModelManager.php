<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Components\Model;

use Doctrine\Common\EventManager;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder as DBALQueryBuilder;
use Doctrine\Inflector\Inflector;
use Doctrine\Inflector\NoopWordInflector;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Exception\MismatchedEventManager;
use Doctrine\ORM\Exception\MissingMappingDriverImplementation;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Proxy\Proxy;
use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use InvalidArgumentException;
use ReflectionProperty;
use RuntimeException;
use Shopware\Components\Model\Query\SqlWalker\ForceIndexWalker;
use Symfony\Component\Validator\ConstraintViolationListInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Traversable;

/**
 * Global Manager which is responsible for initializing the adapter classes.
 */
class ModelManager extends EntityManager
{
    /**
     * Debug mode flag for the query builders.
     *
     * @var bool
     */
    protected $debugMode = false;

    /**
     * @var QueryOperatorValidator
     */
    protected $operatorValidator;

    public function __construct(
        Connection $conn,
        Configuration $config,
        QueryOperatorValidator $operatorValidator
    ) {
        $this->operatorValidator = $operatorValidator;
        parent::__construct($conn, $config);
    }

    /**
     * Factory method to create EntityManager instances.
     *
     * @throws ORMException
     *
     * @return ModelManager
     */
    public static function createInstance(
        Connection $conn,
        Configuration $config,
        ?EventManager $eventManager = null,
        ?QueryOperatorValidator $operatorValidator = null
    ) {
        if (!$config->getMetadataDriverImpl()) {
            throw new MissingMappingDriverImplementation();
        }

        if ($eventManager !== null && $conn->getEventManager() !== $eventManager) {
            throw new MismatchedEventManager();
        }

        if ($operatorValidator === null) {
            $operatorValidator = new QueryOperatorValidator();
        }

        return new self($conn, $config, $operatorValidator);
    }

    /**
     * @return DBALQueryBuilder
     */
    public function getDBALQueryBuilder()
    {
        return new DBALQueryBuilder($this->getConnection());
    }

    /**
     * Serialize an entity or an array of entities to an array
     *
     * @param Traversable|array|ModelEntity $entity
     *
     * @return array
     */
    public function toArray($entity)
    {
        if ($entity instanceof Traversable) {
            $entity = iterator_to_array($entity);
        }

        if (\is_array($entity)) {
            return array_map([$this, 'serializeEntity'], $entity);
        }

        return $this->serializeEntity($entity);
    }

    /**
     * Returns the total count of the passed query builder.
     *
     * @param Query<ModelEntity|array<string, mixed>> $query
     *
     * @return int|null
     */
    public function getQueryCount(Query $query)
    {
        return $this->createPaginator($query)->count();
    }

    /**
     * Returns new instance of Paginator
     *
     * This method should be used instead of
     * new \Doctrine\ORM\Tools\Pagination\Paginator($query).
     *
     * @since 4.1.4
     *
     * @template TModel of (ModelEntity|array<string, mixed>)
     *
     * @param Query<TModel> $query
     *
     * @return Paginator<TModel>
     */
    public function createPaginator(Query $query)
    {
        $paginator = new Paginator($query);
        $paginator->setUseOutputWalkers(false);

        return $paginator;
    }

    /**
     * @return QueryBuilder
     */
    public function createQueryBuilder()
    {
        return new QueryBuilder($this, $this->operatorValidator);
    }

    /**
     * @return ValidatorInterface
     */
    public function getValidator()
    {
        return Shopware()->Container()->get(ValidatorInterface::class);
    }

    /**
     * @param object $object
     *
     * @return ConstraintViolationListInterface
     */
    public function validate($object)
    {
        return $this->getValidator()->validate($object);
    }

    /**
     * @param string[] $tableNames
     */
    public function generateAttributeModels($tableNames = [])
    {
        $generator = $this->createModelGenerator();
        $generator->generateAttributeModels($tableNames);

        $this->regenerateAttributeProxies($tableNames);
    }

    /**
     * Generates Doctrine proxy classes
     *
     * @param string[] $tableNames
     */
    public function regenerateAttributeProxies($tableNames = [])
    {
        $metaDataCache = $this->getConfiguration()->getMetadataCacheImpl();

        if ($metaDataCache !== null && method_exists($metaDataCache, 'deleteAll')) {
            $metaDataCache->deleteAll();
        }

        $allMetaData = $this->getMetadataFactory()->getAllMetadata();
        $proxyFactory = $this->getProxyFactory();

        $attributeMetaData = [];
        foreach ($allMetaData as $metaData) {
            if (!$metaData instanceof ClassMetadata) {
                continue;
            }

            $tableName = $metaData->getTableName();
            if (!str_contains($tableName, '_attributes')) {
                continue;
            }
            if (!empty($tableNames) && !\in_array($tableName, $tableNames, true)) {
                continue;
            }
            $attributeMetaData[] = $metaData;
        }
        $proxyFactory->generateProxyClasses($attributeMetaData);
    }

    /**
     * Generates Doctrine proxy classes
     */
    public function regenerateProxies()
    {
        $metadata = $this->getMetadataFactory()->getAllMetadata();
        $proxyFactory = $this->getProxyFactory();
        $proxyFactory->generateProxyClasses($metadata);
    }

    /**
     * Helper function to add mysql specified command to increase the sql performance.
     *
     * @template TModel of (ModelEntity|array<string, mixed>)
     *
     * @param Query<TModel> $query
     * @param mixed|null    $index        Name of the forced index
     * @param bool          $straightJoin true or false. Allow to add STRAIGHT_JOIN select condition
     * @param bool          $sqlNoCache
     *
     * @return Query<TModel>
     */
    public function addCustomHints(Query $query, $index = null, $straightJoin = false, $sqlNoCache = false)
    {
        $query->setHint(Query::HINT_CUSTOM_OUTPUT_WALKER, ForceIndexWalker::class);

        if ($straightJoin === true) {
            $query->setHint(ForceIndexWalker::HINT_STRAIGHT_JOIN, true);
        }
        if ($index !== null) {
            $query->setHint(ForceIndexWalker::HINT_FORCE_INDEX, $index);
        }
        if ($sqlNoCache === true) {
            $query->setHint(ForceIndexWalker::HINT_SQL_NO_CACHE, true);
        }

        return $query;
    }

    /**
     * Checks if the debug mode for doctrine orm queries is enabled.
     *
     * @return bool
     */
    public function isDebugModeEnabled()
    {
        return $this->debugMode;
    }

    /**
     * Disables the query builder debug mode.
     */
    public function disableDebugMode()
    {
        $this->debugMode = false;
    }

    /**
     * Enables or disables the debug mode of the query builders.
     */
    public function enableDebugMode()
    {
        $this->debugMode = true;
    }

    /**
     * @return Generator
     */
    public function createModelGenerator()
    {
        return new Generator(
            $this->getConnection()->getSchemaManager(),
            $this->getConfiguration()->getAttributeDir(),
            Shopware()->AppPath('Models')
        );
    }

    /**
     * Serialize an entity to an array
     *
     * @author      Boris Guéry <guery.b@gmail.com>
     * @license     http://sam.zoy.org/wtfpl/COPYING
     *
     * @see        http://borisguery.github.com/bgylibrary
     * @see         https://gist.github.com/1034079#file_serializable_entity.php
     *
     * @param ModelEntity|null $entity
     *
     * @return array<string, mixed>
     */
    protected function serializeEntity($entity)
    {
        if ($entity === null) {
            return [];
        }

        if ($entity instanceof Proxy) {
            $entity->__load();
            $className = get_parent_class($entity);
        } else {
            $className = \get_class($entity);
        }
        if (!\is_string($className)) {
            throw new RuntimeException('Could not get class name');
        }
        $metadata = $this->getClassMetadata($className);
        $data = [];
        $inflector = new Inflector(new NoopWordInflector(), new NoopWordInflector());

        foreach ($metadata->fieldMappings as $field => $mapping) {
            if (!($metadata->reflFields[$field] instanceof ReflectionProperty)) {
                throw new InvalidArgumentException(sprintf('Expected an instance of %s', ReflectionProperty::class));
            }

            $data[$field] = $metadata->reflFields[$field]->getValue($entity);
        }

        foreach ($metadata->associationMappings as $field => $mapping) {
            if (!($metadata->reflFields[$field] instanceof ReflectionProperty)) {
                throw new InvalidArgumentException(sprintf('Expected an instance of %s', ReflectionProperty::class));
            }

            $key = $inflector->tableize($field);
            if ($mapping['isCascadeDetach']) {
                $data[$key] = $metadata->reflFields[$field]->getValue($entity);
                if ($data[$key] !== null) {
                    $data[$key] = $this->serializeEntity($data[$key]);
                }
            } elseif ($mapping['isOwningSide'] && $mapping['type'] & ClassMetadata::TO_ONE) {
                $association = $metadata->reflFields[$field]->getValue($entity);
                if (\is_object($association) && $this->getUnitOfWork()->isInIdentityMap($association)) {
                    $data[$key] = $this->getUnitOfWork()->getEntityIdentifier($association);
                } else {
                    // In some case the relationship may not exist, but we want to know about it
                    $data[$key] = null;
                }
            }
        }

        return $data;
    }
}
