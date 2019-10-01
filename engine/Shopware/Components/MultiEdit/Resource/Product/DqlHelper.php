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

namespace Shopware\Components\MultiEdit\Resource\Product;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Article\Article;
use Shopware\Models\Article\Configurator\Group as ConfiguratorGroup;
use Shopware\Models\Article\Configurator\Option as ConfiguratorOption;
use Shopware\Models\Article\Configurator\Set;
use Shopware\Models\Article\Detail;
use Shopware\Models\Article\Image;
use Shopware\Models\Article\Price;
use Shopware\Models\Article\Supplier;
use Shopware\Models\Article\Unit;
use Shopware\Models\Article\Vote;
use Shopware\Models\Attribute\Article as ProductAttribute;
use Shopware\Models\Category\Category;
use Shopware\Models\Property\Group;
use Shopware\Models\Property\Option;
use Shopware\Models\Property\Value as PropertyValue;
use Shopware\Models\Tax\Tax;

/**
 * The dql helper class holds some general helper methods used by various components
 */
class DqlHelper
{
    /**
     * Reference to the PDO object
     *
     * @var \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    protected $db;

    /**
     * Reference to an instance of the EntityManager
     *
     * @var ModelManager
     */
    protected $em;

    /**
     * @var \Enlight_Event_EventManager
     */
    protected $eventManager;

    /**
     * Cached ids of foreign entities
     *
     * @var array
     */
    protected $foreignEntityIDs = [];

    /**
     * All the entities, the user will be able to access via SwagMultiEdit
     */
    protected $entities = [
        [Article::class, 'article'],
        [Detail::class, 'detail'],
        [Supplier::class, 'supplier'],
        [Category::class, 'category'],
        [Unit::class, 'unit'],
        [ProductAttribute::class, 'attribute'],
        [Tax::class, 'tax'],
        [Vote::class, 'vote'],
        [Set::class, 'configuratorSet'],
        [ConfiguratorGroup::class, 'configuratorGroup'],
        [ConfiguratorOption::class, 'configuratorOption'],
        [Group::class, 'propertySet'],
        [Option::class, 'propertyGroup'],
        [PropertyValue::class, 'propertyOption'],
        [Price::class, 'price'],
        [Vote::class, 'vote'],
        [Image::class, 'image'],
    ];

    protected $columnsNotToShowInGrid = [
        'Tax_tax',
        'Detail_kind',
        'Detail_articleId',
        'Detail_unitId',
        'Article_template',
        'Article_configuratorSetId',
        'Article_description',
        'Article_descriptionLong',
        'Article_mode',
        'Article_filterGroupId',
        'Article_priceGroupId',
        'Article_mainDetailId',
        'Article_availableFrom',
        'Article_availableTo',
        'Article_supplierId',
        'Article_taxId',
        'Article_crossBundleLook',
        'Supplier_image',
        'Supplier_metaDescription',
        'Supplier_metaKeywords',
        'Supplier_metaTitle',
        'Supplier_changed',
        'Supplier_description',
        'Supplier_link',
        'Attribute_articleId',
        'Attribute_articleDetailId',
    ];

    /**
     * Some mappings we will need later
     */
    protected $attributeToEntityMapping = [];

    protected $attributeToColumn = [];

    protected $entityToPrefix = [];

    protected $prefixToEntity = [];

    protected $columns = [];

    protected $columnInfo = [];

    public function __construct(
        \Enlight_Components_Db_Adapter_Pdo_Mysql $db,
        ModelManager $em,
        \Enlight_Event_EventManager $eventManager
    ) {
        $this->db = $db;
        $this->em = $em;
        $this->eventManager = $eventManager;

        $this->buildMapping();
    }

    /**
     * Returns a reference to the Doctrine EntityManager
     *
     * @return ModelManager
     */
    public function getEntityManager()
    {
        return $this->em;
    }

    /**
     * Returns a reference to our PDO object
     *
     * @return \Enlight_Components_Db_Adapter_Pdo_Mysql
     */
    public function getDb()
    {
        return $this->db;
    }

    /**
     * @return \Enlight_Event_EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Returns all entities as an array of entities ([0]) and their alias ([1])
     *
     * @return array
     */
    public function getEntities()
    {
        return $this->entities;
    }

    /**
     * Returns the column name for a given attribute. This basically means, that the case of the attribute is fixed
     * e.g. ARTICLE.ID => id
     *
     * @param string $attribute
     */
    public function getColumnForAttribute($attribute)
    {
        $attribute = strtoupper($attribute);

        return $this->attributeToColumn[$attribute];
    }

    /**
     * Returns the doctrine entity associated to an attribute
     * e.g. ARTICLE.ID => \Shopware\Models\Article\Article
     *
     * @param string $attribute
     */
    public function getEntityForAttribute($attribute)
    {
        $attribute = strtoupper($attribute);

        return $this->attributeToEntityMapping[$attribute];
    }

    /**
     * Returns the main entity we'll never need to join manually
     */
    public function getMainEntity()
    {
        return Detail::class;
    }

    /**
     * Returns the prefix for a given entity.
     * e.g. article => \Shopware\Models\Article\Article
     *
     * @param string $prefix
     */
    public function getEntityForPrefix($prefix)
    {
        return $this->prefixToEntity[$prefix];
    }

    /**
     * Returns the prefix for a given entity
     * e.g. \Shopware\Models\Article\Article => article
     *
     * @param string $entity
     */
    public function getPrefixForEntity($entity)
    {
        return $this->entityToPrefix[$entity];
    }

    /**
     * Returns a list of columns which are always visible in the default filter view
     *
     * @return array
     */
    public function getDefaultColumns()
    {
        return [
            'Detail_number',
            'Article_name',
            'Supplier_name',
            'Article_active',
            'Detail_active',
            'Price_price',
            'Tax_name',
            'Detail_inStock',
        ];
    }

    /**
     * Returns a list which holds the configuration for all known columns - e.g. name, data type, associated table…
     *
     * Columns having "allowInGrid" set to true, are selected for the main product listing.
     *
     * @return array
     */
    public function getColumnsForProductListing()
    {
        return $this->columnInfo;
    }

    /**
     * Returns a single row with (almost) all possibly relevant information of a product
     *
     * @param int $detailId
     */
    public function getProductForListing($detailId)
    {
        $products = $this->getProductsForListing([$detailId]);

        return array_shift($products);
    }

    /**
     * Returns a multiple row with (almost) all possibly relevant information of products
     *
     * @param int[] $ids
     *
     * @return array[]
     */
    public function getProductsForListing(array $ids)
    {
        $columns = $this->getColumnsForProductListing();

        $select = [];
        foreach ($columns as $key => $config) {
            if (!$config['allowInGrid']) {
                continue;
            }

            // Allow custom select statements
            if (!empty($config['selectClause'])) {
                $select[] = "{$config['selectClause']} as `{$config['alias']}`";
            } else {
                $select[] = "{$config['table']}.{$config['columnName']} as `{$config['alias']}`";
            }
        }

        $select = implode(",\n            ", $select);

        $sql = "
            SELECT

            {$select}

            FROM `s_articles_details` s_articles_details

            LEFT JOIN `s_articles`
            ON s_articles.id = s_articles_details.articleID

            INNER JOIN `s_articles_attributes`
            ON s_articles_attributes.articledetailsID = s_articles_details.id

            LEFT JOIN `s_articles_supplier`
            ON s_articles_supplier.id = s_articles.supplierID

            LEFT JOIN `s_articles_prices`
            ON s_articles_prices.articledetailsID = s_articles_details.id
            AND s_articles_prices.from = 1
            AND s_articles_prices.pricegroup = 'EK'

            LEFT JOIN `s_core_tax`
            ON s_core_tax.id = s_articles.taxID

            WHERE s_articles_details.id IN (:ids)
        ";

        $products = $this->em->getConnection()->fetchAll(
            $sql,
            ['ids' => $ids],
            ['ids' => Connection::PARAM_INT_ARRAY]
        );

        return $this->addInfo($products);
    }

    /**
     * Returns a merged list of base columns
     *
     * @return array
     */
    public function getColumns()
    {
        return $this->columns;
    }

    /**
     * Returns an array of all attributes
     *
     * @return array
     */
    public function getAttributes()
    {
        return array_keys($this->attributeToColumn);
    }

    /**
     * Build some basic mappings
     */
    public function buildMapping()
    {
        foreach ($this->entities as $entity) {
            list($entity, $prefix) = $entity;

            $this->entityToPrefix[$entity] = $prefix;
            $this->prefixToEntity[$prefix] = $entity;
        }

        foreach ($this->entities as $entity) {
            list($entity, $prefix) = $entity;

            $columns = $this->getPrefixedColumns($entity);

            foreach ($columns as $column) {
                $this->columns[$column] = $column;
                $normalizedColumn = strtoupper($column);
                if (!isset($this->attributeToEntityMapping[$normalizedColumn])) {
                    $this->attributeToEntityMapping[$normalizedColumn] = $entity;
                }
                if (!isset($this->attributeToColumn[$normalizedColumn])) {
                    $this->attributeToColumn[$normalizedColumn] = $column;
                }
            }
        }

        $this->buildColumnInfo();
    }

    /**
     * Build a list which holds the configuration for all known columns - e.g. name, data type, associated table…
     *
     * Columns having "allowInGrid" set to true, are selected for the main product listing.
     */
    public function buildColumnInfo()
    {
        $shownColumns = $this->getDefaultColumns();
        $columnPositions = array_flip($shownColumns);

        $result = [];

        foreach ($this->getEntities() as $entityArray) {
            list($entity, $prefix) = $entityArray;
            if ($prefix === 'price') {
                continue;
            }
            $entityShort = ucfirst($prefix);

            $metadata = $this->getEntityManager()->getClassMetadata($entity);

            $fields = $metadata->fieldMappings;

            foreach ($fields as $name => $config) {
                $alias = $entityShort . '_' . $name;
                $key = $entityShort . ucfirst($name);
                $mapping = $metadata->getFieldMapping($name);
                $result[$key] = [
                    'entity' => $entityShort,
                    'field' => $name,
                    'editable' => substr($name, -2) !== 'Id' && $name !== 'id' && substr($name, -2) !== 'ID' && $entity !== Tax::class && $entity !== Supplier::class,
                    'type' => $config['type'],
                    'precision' => $config['precision'],
                    'nullable' => (bool) $config['nullable'],
                    'columnName' => $mapping['columnName'],
                    'table' => $metadata->getTableName(),
                    'alias' => $alias,
                    'show' => in_array($alias, $shownColumns),
                    'position' => array_key_exists($alias, $columnPositions) ? $columnPositions[$alias] : -1,
                ];

                $result[$key]['allowInGrid'] = $this->showColumnInGrid($result[$key]);
            }
        }

        $alias = 'Price_price';
        $result['PricePrice'] = [
            'entity' => 'Price',
            'field' => 'price',
            'editable' => true,
            'type' => 'float',
            'selectClause' => 'ROUND(s_articles_prices.price*(100+s_core_tax.tax)/100,2)',
            'precision' => 3,
            'nullable' => false,
            'columnName' => 'price',
            'table' => 's_articles_prices',
            'alias' => $alias,
            'allowInGrid' => true,
            'show' => in_array($alias, $shownColumns),
            'position' => array_key_exists($alias, $columnPositions) ? $columnPositions[$alias] : -1,
        ];

        $alias = 'Price_pseudoPrice';
        $result['PricePseudoPrice'] = [
            'entity' => 'Price',
            'field' => 'pseudoPrice',
            'editable' => true,
            'type' => 'float',
            'selectClause' => 'ROUND(s_articles_prices.pseudoprice*(100+s_core_tax.tax)/100,2)',
            'precision' => 3,
            'nullable' => true,
            'columnName' => 'pseudoprice',
            'table' => 's_articles_prices',
            'alias' => $alias,
            'allowInGrid' => true,
            'show' => in_array($alias, $shownColumns),
            'position' => array_key_exists($alias, $columnPositions) ? $columnPositions[$alias] : -1,
        ];

        $alias = 'Price_netPrice';
        $result['PriceNetPrice'] = [
            'entity' => 'Price',
            'field' => 'price',
            'editable' => false,
            'type' => 'float',
            'precision' => 3,
            'nullable' => false,
            'columnName' => 'price',
            'table' => 's_articles_prices',
            'alias' => 'Price_netPrice',
            'allowInGrid' => true,
            'show' => in_array($alias, $shownColumns),
            'position' => array_key_exists($alias, $columnPositions) ? $columnPositions[$alias] : -1,
        ];

        // Sort columns by position
        uasort(
            $result,
            function ($a, $b) {
                $a = $a['position'];
                $b = $b['position'];
                if ($a == $b) {
                    return 0;
                }

                return ($a > $b) ? 1 : -1;
            }
        );

        // Allow users to add his own columns
        $result = $this->getEventManager()->filter(
            'SwagMultiEdit_Product_DqlHelper_getColumnsForProductListing_filterColumns',
            $result,
            ['subject' => $this, 'defaultColumns' => $shownColumns]
        );

        return $this->columnInfo = $result;
    }

    /**
     * Return column info for a given alias
     *
     * @param string $alias
     *
     * @return array|false
     */
    public function getColumnInfoByAlias($alias)
    {
        foreach ($this->columnInfo as $info) {
            if ($info['alias'] == $alias) {
                return $info;
            }
        }

        return false;
    }

    /**
     * Returns the association name for a given entity in order to join it automatically
     *
     * @param string $entity
     *
     * @return string
     */
    public function getAssociationForEntity($entity)
    {
        // Some custom references
        switch ($entity) {
            case Category::class:
                return 'article.allCategories';
                break;

            case Image::class:
                return 'article.images';
                break;
        }

        // Some generic searching for the association
        $metaData = $this->getEntityManager()->getClassMetadata(Detail::class);
        foreach ($metaData->associationMappings as $mapping) {
            if ($mapping['targetEntity'] == $entity) {
                return 'detail.' . $mapping['fieldName'];
            }
        }

        $metaData = $this->getEntityManager()->getClassMetadata(Article::class);
        foreach ($metaData->associationMappings as $mapping) {
            if ($mapping['targetEntity'] == $entity) {
                return 'article.' . $mapping['fieldName'];
            }
        }

        $metaData = $this->getEntityManager()->getClassMetadata(Set::class);
        foreach ($metaData->associationMappings as $mapping) {
            if ($mapping['targetEntity'] == $entity) {
                return 'configuratorSet.' . $mapping['fieldName'];
            }
        }

        $metaData = $this->getEntityManager()->getClassMetadata(Group::class);
        foreach ($metaData->associationMappings as $mapping) {
            if ($mapping['targetEntity'] == $entity) {
                return 'propertySet.' . $mapping['fieldName'];
            }
        }
    }

    /**
     * Returns a list of entities we need to join based on the given tokens
     *
     * @param array $tokens
     *
     * @return array
     */
    public function getJoinColumns($tokens)
    {
        $join = [];
        foreach ($tokens as $token) {
            if ($token['type'] === 'attribute') {
                $entity = $this->getEntityForAttribute($token['token']);
                // Do not allow main entity to be joined
                if ($entity == $this->getMainEntity()) {
                    continue;
                }
                // In some cases, additional joins are needed - e.g. a ConfiguratorGroup does need the ConfiguratorSet
                switch ($entity) {
                    case ConfiguratorGroup::class:
                        $join[Set::class] = Set::class;
                        break;
                    case Option::class:
                        $join[Group::class] = Group::class;
                        break;
                    case Image::class:
                        break;
                }

                // Default: Join the associated entity
                $join[$entity] = $entity;
            } elseif ($token['token'] === 'HASBLOCKPRICE') {
                $join[Price::class] = Price::class;
            } elseif ($token['token'] === 'HASIMAGE' || $token['token'] === 'HASNOIMAGE') {
                $join[Image::class] = Image::class;
            }
        }

        // Allow users to add his own joins depending on the passed tokens
        $join = $this->getEventManager()->filter(
            'SwagMultiEdit_Product_DqlHelper_getJoinColumns_filterColumns',
            $join,
            ['subject' => $this, 'tokens' => $tokens]
        );

        return $join;
    }

    /**
     * Returns DQL for the token list. Will basically fix the case of the attributes and replace some
     * convenient operators with proper expressions understood by DQL
     *
     * @param array $tokens
     *
     * @return array
     */
    public function getDqlFromTokens($tokens)
    {
        $params = [];
        $newTokens = [];

        foreach ($tokens as $key => $token) {
            // Allow anyone to subscribe to any token and replace it with his own logic
            // Also allows you to add own tokens
            if ($event = $this->getEventManager()->notifyUntil(
                'SwagMultiEdit_Product_DqlHelper_getDqlFromTokens_Token_' . ucfirst(strtolower($token['type'])),
                [
                    'subject' => $this,
                    'currentToken' => $token,
                    'alltokens' => $tokens,
                    'processedTokens' => $newTokens,
                ]
            )
            ) {
                $return = $event->getReturn();
                if (!is_array($return)) {
                    $newTokens[] = $return;
                } else {
                    $newToken[] = $return[0];
                    $params[] = $return[1];
                }
                continue;
            }

            // RegExp handling
            $lastToken = $tokens[$key - 1]['token'];
            if ($lastToken === '~' || $lastToken === '!~') {
                // Pop the last operator (~ OR !~)
                array_pop($newTokens);
                // Pop the attribute
                $attribute = array_pop($newTokens);

                // As we are in the where-clause, we need a comparison
                $mode = $lastToken === '~' ? 1 : 0;

                // Build the DQL Token - we've registered our own RegExp DoctrineExtension before
                $newTokens[] = ' RegExp (?' . count($params) . ", {$attribute}) = {$mode}";
                // Consume the next token as param and skip it in the next iteration
                $params[] = substr($token['token'], 1, -1);
                continue;
            }

            // Quoting value tokens:
            if ($token['type'] === 'values') {
                $newTokens[] = '?' . count($params);
                // Non-numeric tokens will become their quotes removed:
                if (!is_numeric($token['token'])) {
                    $params[] = substr($token['token'], 1, -1);
                // Numeric tokens can simple be appended to the params array
                } else {
                    $params[] = $token['token'];
                }
                continue;
            }
            // Get the correct column name based on the attribute name
            if ($token['type'] === 'attribute') {
                $newTokens[] = $this->getColumnForAttribute($token['token']);
                continue;
            }

            // Replace some convenience operators back
            // Switch is considered a looping structure by php
            // we need to continue two levels!
            if (strpos($token['type'], 'Operators')) {
                switch (trim($token['token'])) {
                    case '=':
                        $newTokens[] = 'LIKE';
                        continue 2;
                    case 'ISTRUE':
                        $newTokens[] = ' = 1 ';
                        continue 2;
                    case 'ISFALSE':
                        $newTokens[] = ' = 0 ';
                        continue 2;
                    case 'HASIMAGE':
                        $newTokens[] = ' image.id IS NOT NULL ';
                        continue 2;
                    case 'HASNOIMAGE':
                        $newTokens[] = ' image.id IS NULL ';
                        continue 2;
                    case 'ISNULL':
                        $newTokens[] = ' IS NULL ';
                        continue 2;
                    case 'HASPROPERTIES':
                        $newTokens[] = ' article.filterGroupId > 0';
                        continue 2;
                    case 'HASCONFIGURATOR':
                        $newTokens[] = ' article.configuratorSetId > 0';
                        continue 2;
                    case 'HASBLOCKPRICE':
                        $newTokens[] = ' price.from > 1';
                        continue 2;
                    case 'ISMAIN':
                        $newTokens[] = ' detail.kind = 1 ';
                        continue 2;
                }
            }

            $newTokens[] = $token['token'];
        }

        // Allow users to modify the token array afterwards
        $filteredArray = $this->getEventManager()->filter(
            'SwagMultiEdit_Product_DqlHelper_getDqlFromTokens_filterTokens',
            ['tokens' => $newTokens, 'params' => $params],
            ['subject' => $this, 'tokens' => $tokens]
        );
        $newTokens = $filteredArray['tokens'];
        $params = $filteredArray['params'];

        return [implode(' ', $newTokens), $params];
    }

    /**
     * Helper function to format a value depending on its type and value
     * Will set value = null for empty *strings* and replace comma with period for decimals
     *
     * @param string $prefix
     * @param array  $field
     * @param string $value
     *
     * @return mixed|null
     */
    public function formatValue($prefix, $field, $value)
    {
        $info = $this->getColumnsForProductListing();
        $info = $info[ucfirst($prefix) . ucfirst($field['field'])];

        if ($info['nullable'] && $value == '') {
            $value = null;
        }

        $type = $info['type'];
        if ($value && ($type === 'decimal' || $type === 'integer' || $type === 'float')) {
            $value = str_replace(',', '.', $value);
        }

        return $value;
    }

    /**
     * This method will return a list of IDs of a given foreign entity which is connected to a given $detailId
     *
     * @param string $foreignPrefix
     * @param int[]  $detailIds
     */
    public function getIdForForeignEntity($foreignPrefix, $detailIds)
    {
        $foreignPrefix = strtolower($foreignPrefix);

        $key = $foreignPrefix . '-' . implode('_', $detailIds);
        $value = $this->foreignEntityIDs[$key];

        if (!$value) {
            $value = $this->getIdForForeignEntityInternal($foreignPrefix, $detailIds);
            $this->foreignEntityIDs[$key] = $value;
        }

        return $value;
    }

    /**
     * Internal method that will return a list of IDs of a given foreign entity which is connected to a given $detailIds
     *
     * @param string $foreignPrefix
     * @param int[]  $detailIds
     */
    public function getIdForForeignEntityInternal($foreignPrefix, $detailIds)
    {
        $quoted = '(' . $this->getDb()->quote($detailIds, \PDO::PARAM_INT) . ');';

        switch ($foreignPrefix) {
            case 'attribute':
                return $this->getDb()->fetchCol(
                    'SELECT id FROM s_articles_attributes WHERE articledetailsID IN ' . $quoted
                );

            case 'article':
                return $this->getDb()->fetchCol('SELECT articleID FROM s_articles_details WHERE id  IN ' . $quoted);

            case 'detail':
                return $detailIds;

            case 'supplier':
                return $this->getDb()->fetchCol(
                    'SELECT supplierID
                     FROM s_articles_details
                     INNER JOIN s_articles
                        ON s_articles.id = s_articles_details.articleID
                     WHERE s_articles_details.id  IN ' . $quoted
                );

            case 'price':
                return $this->getDb()->fetchCol(
                    'SELECT s_articles_prices.id
                    FROM s_articles_prices
                    WHERE s_articles_prices.articledetailsID  IN ' . $quoted
                );

            case 'vote':
                return $this->getDb()->fetchCol(
                    'SELECT s_articles_vote.id
                    FROM s_articles_details
                    LEFT JOIN s_articles_vote
                        ON s_articles_vote.articleID = s_articles_details.articleID
                    WHERE s_articles_details.id  IN ' . $quoted
                );

            case 'tax':
                return $this->getDb()->fetchCol(
                    'SELECT taxID
                     FROM s_articles_details
                     INNER JOIN s_articles
                        ON s_articles.id = s_articles_details.articleID
                     WHERE s_articles_details.id  IN ' . $quoted
                );

            case 'category':
                return $this->getDb()->fetchCol(
                    'SELECT s_articles_categories_ro.categoryID
                    FROM s_articles_details
                    LEFT JOIN s_articles_categories_ro
                        ON s_articles_categories_ro.articleID = s_articles_details.articleID

                    WHERE s_articles_details.id  IN ' . $quoted
                );

            case 'configuratorset':
                return $this->getDb()->fetchCol(
                    'SELECT configurator_set_id
                     FROM s_articles_details
                     INNER JOIN s_articles
                        ON s_articles.id = s_articles_details.articleID
                     WHERE s_articles_details.id  IN ' . $quoted
                );

            case 'configuratorgroup':
                return $this->getDb()->fetchCol(
                    'SELECT group_id
                     FROM s_articles_details
                     INNER JOIN s_articles
                        ON s_articles.id = s_articles_details.articleID
                     INNER JOIN s_article_configurator_set_group_relations
                        ON s_articles.configurator_set_id = s_article_configurator_set_group_relations.set_id
                     WHERE s_articles_details.id  IN ' . $quoted
                );

            case 'configuratoroption':
                return $this->getDb()->fetchCol(
                    'SELECT option_id
                     FROM s_article_configurator_option_relations
                     WHERE article_id  IN ' . $quoted
                );

            case 'propertyset':
                return $this->getDb()->fetchCol(
                    'SELECT filtergroupID
                     FROM s_articles_details
                     INNER JOIN s_articles
                        ON s_articles.id = s_articles_details.articleID
                     WHERE s_articles_details.id  IN ' . $quoted
                );

            case 'propertygroup':
                return $this->getDb()->fetchCol(
                    'SELECT optionID
                     FROM s_articles_details
                     INNER JOIN s_articles
                        ON s_articles.id = s_articles_details.articleID
                     INNER JOIN s_filter_relations
                        ON groupID = s_articles.filtergroupID
                     WHERE s_articles_details.id  IN ' . $quoted
                );

            case 'propertyoption':
                return $this->getDb()->fetchCol(
                    'SELECT valueID
                     FROM s_articles_details
                     INNER JOIN s_filter_articles
                        ON s_filter_articles.articleID = s_articles_details.articleID
                     WHERE s_articles_details.id  IN ' . $quoted
                );

            case 'unit':
                return $this->getDb()->fetchCol(
                    'SELECT unitID
                    FROM s_articles_details
                    WHERE s_articles_details.id IN ' . $quoted
                );
        }

        throw new \RuntimeException(sprintf('Foreign table %s not defined, yet. Please report this error.', $foreignPrefix));
    }

    /**
     * Groups a given list of operations by the entity they operate on
     *
     * @param array $operations
     *
     * @return array
     */
    public function groupOperations($operations)
    {
        // Group operations by prefix and replace the attribute by the corresponding column
        $outputOperations = [];
        foreach ($operations as $operation) {
            $operation['column'] = $this->getColumnForAttribute($operation['column']);
            list($prefix, $column) = explode('.', $operation['column']);

            if (!isset($outputOperations[$prefix])) {
                $outputOperations[$prefix] = [];
            }
            $outputOperations[$prefix][] = $operation;
        }

        return $outputOperations;
    }

    /**
     * Returns all columns for a given entity prefixed
     * eg.g \Shopware\Models\Article\Article => array('id', 'name', …)
     *
     * @param string $entity
     *
     * @return array
     */
    protected function getPrefixedColumns($entity)
    {
        $columns = array_keys($this->getEntityManager()->getClassMetadata($entity)->columnNames);
        foreach ($columns as &$column) {
            $column = $this->entityToPrefix[$entity] . '.' . $column;
        }

        return $columns;
    }

    /**
     * Will add additional information. Does the product
     *
     *  * have a configurator
     *  * a category
     *  * images?
     *
     * @param array[] $articles
     *
     * @return array[]
     */
    protected function addInfo(array $articles)
    {
        $articles = array_filter($articles);

        if (empty($articles)) {
            return $articles;
        }
        $ids = array_filter(array_column($articles, 'Article_id'));

        if (empty($ids)) {
            return $articles;
        }

        $implode = array_map(function ($id) {
            return (int) $id;
        }, $ids);

        $qb = $this->em->getConnection()->createQueryBuilder();
        $images = $qb->from('s_articles_img', 'img')
            ->addSelect('img.articleID, img.img')
            ->where('img.articleID IN (:ids)')
            ->andWhere('img.main = 1')
            ->andWhere('img.article_detail_id IS NULL')
            ->setParameter('ids', $implode, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_KEY_PAIR);

        $qb = $this->em->getConnection()->createQueryBuilder();
        $categories = $qb->from('s_articles_categories_ro', 'cat')
            ->addSelect('DISTINCT articleID')
            ->where('cat.articleID IN (:ids)')
            ->setParameter('ids', $implode, Connection::PARAM_INT_ARRAY)
            ->execute()
            ->fetchAll(\PDO::FETCH_COLUMN);

        foreach ($articles as &$product) {
            $id = $product['Article_id'];

            $product['hasConfigurator'] = !empty($product['Article_configuratorSetId']);
            $product['imageSrc'] = null;

            if (array_key_exists($id, $images)) {
                $product['imageSrc'] = $images[$id] . '_140x140.jpg';
            }

            $product['hasCategories'] = in_array($id, $categories, true);
        }

        return $articles;
    }

    /**
     * Decide if a column can be shown in the grid
     *
     * Use the filter event SwagMultiEdit_Product_DqlHelper_getColumnsForProductListing_filterColumns
     * in order to overwrite the selectable columns
     *
     * @param array $column
     *
     * @return bool
     */
    private function showColumnInGrid($column)
    {
        $entitiesToShow = [
            'Article',
            'Tax',
            'Detail',
            'Supplier',
            'Attribute',
        ];

        // By default show the main entities
        $show = in_array($column['entity'], $entitiesToShow);

        // If column was blacklisted explicitly, set $show = false
        if (in_array($column['alias'], $this->columnsNotToShowInGrid)) {
            $show = false;
        }

        return $show;
    }
}
