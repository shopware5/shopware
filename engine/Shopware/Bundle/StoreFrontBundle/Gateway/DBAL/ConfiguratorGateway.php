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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Shopware\Bundle\SearchBundle\Facet\VariantFacet;
use Shopware\Bundle\SearchBundleDBAL\VariantHelper;
use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class ConfiguratorGateway implements Gateway\ConfiguratorGatewayInterface
{
    /**
     * @var Hydrator\ConfiguratorHydrator
     */
    private $configuratorHydrator;

    /**
     * The FieldHelper class is used for the
     * different table column definitions.
     *
     * This class helps to select each time all required
     * table data for the store front.
     *
     * Additionally the field helper reduce the work, to
     * select in a second step the different required
     * attribute tables for a parent table.
     *
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var \Shopware\Bundle\StoreFrontBundle\Gateway\MediaGatewayInterface
     */
    private $mediaGateway;

    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var VariantHelper
     */
    private $variantHelper;

    /**
     * @param Connection                                                      $connection
     * @param FieldHelper                                                     $fieldHelper
     * @param Hydrator\ConfiguratorHydrator                                   $configuratorHydrator
     * @param \Shopware\Bundle\StoreFrontBundle\Gateway\MediaGatewayInterface $mediaGateway
     * @param VariantHelper                                                   $variantHelper
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\ConfiguratorHydrator $configuratorHydrator,
        Gateway\MediaGatewayInterface $mediaGateway,
        VariantHelper $variantHelper
    ) {
        $this->connection = $connection;
        $this->configuratorHydrator = $configuratorHydrator;
        $this->fieldHelper = $fieldHelper;
        $this->mediaGateway = $mediaGateway;
        $this->variantHelper = $variantHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function get(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $query = $this->getQuery();
        $query->addSelect($this->fieldHelper->getConfiguratorSetFields())
            ->addSelect($this->fieldHelper->getConfiguratorGroupFields())
            ->addSelect($this->fieldHelper->getConfiguratorOptionFields())
        ;

        $this->fieldHelper->addConfiguratorGroupTranslation($query, $context);
        $this->fieldHelper->addConfiguratorOptionTranslation($query, $context);

        $query->where('products.id = :id')
            ->setParameter(':id', $product->getId());

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $this->configuratorHydrator->hydrate($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguratorMedia(Struct\BaseProduct $product, Struct\ShopContextInterface $context)
    {
        $subQuery = $this->connection->createQueryBuilder();

        $subQuery->select('image.media_id')
            ->from('s_articles_img', 'image')
            ->innerJoin('image', 's_article_img_mappings', 'mapping', 'mapping.image_id = image.id')
            ->innerJoin('mapping', 's_article_img_mapping_rules', 'rules', 'rules.mapping_id = mapping.id')
            ->where('image.articleID = product.id')
            ->andWhere('rules.option_id = optionRelation.option_id')
            ->orderBy('image.position')
            ->setMaxResults(1)
        ;

        $query = $this->connection->createQueryBuilder();

        $query->select([
            'optionRelation.option_id',
            '(' . $subQuery->getSQL() . ') as media_id',
        ]);

        $query->from('s_articles', 'product')
            ->innerJoin('product', 's_article_configurator_set_option_relations', 'optionRelation', 'product.configurator_set_id = optionRelation.set_id')
            ->where('product.id = :articleId')
            ->groupBy('optionRelation.option_id')
            ->setParameter(':articleId', $product->getId());

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);
        $data = array_filter($data);

        $media = $this->mediaGateway->getList($data, $context);

        $result = [];
        foreach ($data as $optionId => $mediaId) {
            if (!isset($media[$mediaId])) {
                continue;
            }
            $result[$optionId] = $media[$mediaId];
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getProductCombinations(Struct\BaseProduct $product)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'relations.option_id',
            "GROUP_CONCAT(DISTINCT assignedRelations.option_id, '' SEPARATOR '|') as combinations",
        ]);

        $query->from('s_article_configurator_option_relations', 'relations')
            ->innerJoin('relations', 's_articles_details', 'variant', 'variant.id = relations.article_id AND variant.articleID = :articleId AND variant.active = 1')
            ->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID AND (product.laststock * variant.instock) >= (product.laststock * variant.minpurchase)')
            ->leftJoin('relations', 's_article_configurator_option_relations', 'assignedRelations', 'assignedRelations.article_id = relations.article_id AND assignedRelations.option_id != relations.option_id')
            ->groupBy('relations.option_id')
            ->setParameter(':articleId', $product->getId());

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_KEY_PAIR);

        foreach ($data as &$row) {
            $row = explode('|', $row);
        }

        return $data;
    }


    /**
     * Get possible combinations of all products
     *
     * @param array $numbers
     * @param Struct\ShopContextInterface $context
     * @return array
     */
    public function getProductsCombinations(array $numbers, Struct\ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'variant.articleID',
            "GROUP_CONCAT(DISTINCT relations.option_id ORDER BY relations.option_id  SEPARATOR '-') as combinations",
        ]);

        $query->from('s_article_configurator_option_relations', 'relations');
        $query->innerJoin('relations', 's_articles_details', 'variant', 'variant.id = relations.article_id AND variant.ordernumber IN (:numbers) AND variant.active = 1');
        $query->innerJoin('variant', 's_articles', 'product', 'product.id = variant.articleID AND (product.laststock * variant.instock) >= (product.laststock * variant.minpurchase)');
        $query->addGroupBy('variant.articleID');
        $query->addGroupBy('variant.id');

        $query->setParameter('numbers', $numbers, Connection::PARAM_STR_ARRAY);

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_GROUP|\PDO::FETCH_COLUMN);
    }

    /**
     * Fetches  all groups with all options for provided products
     *
     * @param array $numbers
     * @param Struct\ShopContextInterface $context
     * @return Struct\Configurator\Group[]
     */
    public function getConfigurations(array $numbers, Struct\ShopContextInterface $context)
    {
        $query = $this->connection->createQueryBuilder();
        $query->addSelect('product.id as array_key');

        $query->addSelect($this->fieldHelper->getConfiguratorSetFields())
            ->addSelect($this->fieldHelper->getConfiguratorGroupFields())
            ->addSelect($this->fieldHelper->getConfiguratorOptionFields())
        ;

        $this->fieldHelper->addConfiguratorGroupTranslation($query, $context);
        $this->fieldHelper->addConfiguratorOptionTranslation($query, $context);


        $query->from('s_articles', 'product');
        $query->innerJoin('product', 's_articles_details', 'variant', 'variant.articleID = product.id');
        $query->innerJoin('product', 's_article_configurator_sets', 'configuratorSet', 'configuratorSet.id = product.configurator_set_id');
        $query->innerJoin('configuratorSet', 's_article_configurator_set_group_relations', 'groupRelation', 'groupRelation.set_id = configuratorSet.id');
        $query->innerJoin('configuratorSet', 's_article_configurator_set_option_relations', 'optionRelation', 'optionRelation.set_id = configuratorSet.id');
        $query->innerJoin('groupRelation', 's_article_configurator_groups', 'configuratorGroup', 'configuratorGroup.id = groupRelation.group_id');
        $query->innerJoin('optionRelation', 's_article_configurator_options', 'configuratorOption', 'configuratorOption.id = optionRelation.option_id AND configuratorOption.group_id = configuratorGroup.id');
        $query->leftJoin('configuratorGroup', 's_article_configurator_groups_attributes', 'configuratorGroupAttribute', 'configuratorGroupAttribute.groupID = configuratorGroup.id');
        $query->leftJoin('configuratorOption', 's_article_configurator_options_attributes', 'configuratorOptionAttribute', 'configuratorOptionAttribute.optionID = configuratorOption.id');

        $query->addOrderBy('configuratorGroup.position');
        $query->addOrderBy('configuratorGroup.name');
        $query->addOrderBy('configuratorOption.position');
        $query->addOrderBy('configuratorOption.name');

        $query->addGroupBy('product.id');
        $query->addGroupBy('configuratorOption.id');

        $query->where('variant.ordernumber IN (:numbers)');
        $query->setParameter('numbers', $numbers, Connection::PARAM_STR_ARRAY);

        $data = $query->execute()->fetchAll(\PDO::FETCH_GROUP);

        $result = [];
        foreach ($data as $productId => $rows) {
            $result[$productId] = $this->configuratorHydrator->hydrateGroups($rows);
        }

        return $result;
    }






    public function getVariantGroups(array $ordernumbers, Struct\ShopContextInterface $context)
    {
        if (empty($ordernumbers)) {
            return [];
        }

        /**
         * @var VariantFacet
         */
        $variantFacet = $this->variantHelper->getVariantFacet();

        if (empty($variantFacet)) {
            return [];
        }

        $expandGroups = $variantFacet->getExpandGroupIds();

        $query = $this->connection->createQueryBuilder();
        $query->select('DISTINCT articleId')
            ->from('s_articles_details')
            ->where('ordernumber IN (:products)')
            ->setParameter(':products', $ordernumbers, Connection::PARAM_STR_ARRAY);
        $articleIds = $query->execute()->fetchAll(\PDO::FETCH_COLUMN);

        $query = $this->connection->createQueryBuilder();
        $query->select(['variant.articleId', 'options.group_id', "GROUP_CONCAT(DISTINCT relations.option_id SEPARATOR '|') as options"])
            ->from('s_articles_details', 'variant')
            ->innerJoin('variant', 's_article_configurator_option_relations', 'relations', 'variant.id = relations.article_id')
            ->innerJoin('relations', 's_article_configurator_options', 'options', 'options.id = relations.option_id')
            ->andWhere('variant.articleID IN (:products)')
            ->setParameter(':products', $articleIds, Connection::PARAM_INT_ARRAY)
            ->addGroupBy('variant.articleId')
            ->addGroupBy('options.group_id');

        if (!empty($expandGroups)) {
            $query->andWhere('NOT options.group_id in (:group_ids)')
            ->setParameter(':group_ids', $expandGroups, Connection::PARAM_INT_ARRAY);
        }

        $articles = $query->execute()->fetchAll(\PDO::FETCH_ASSOC);

        $groups = [];
        foreach ($articles as $article) {
            $group = new Struct\Configurator\Group();
            $group->setId($article['group_id']);

            $options = explode('|', $article['options']);
            foreach ($options as $option) {
                $opt = new Struct\Configurator\Option();
                $opt->setId($option);
                $group->addOption($opt);
            }

            if (empty($groups[$article['articleId']])) {
                $groups[$article['articleId']] = [];
            }

            array_push($groups[$article['articleId']], $group);
        }

        return $groups;
    }

    /**
     * @return QueryBuilder
     */
    private function getQuery()
    {
        $query = $this->connection->createQueryBuilder();

        $query->from('s_article_configurator_sets', 'configuratorSet')
            ->innerJoin('configuratorSet', 's_articles', 'products', 'products.configurator_set_id = configuratorSet.id')
            ->innerJoin('configuratorSet', 's_article_configurator_set_group_relations', 'groupRelation', 'groupRelation.set_id = configuratorSet.id')
            ->innerJoin('groupRelation', 's_article_configurator_groups', 'configuratorGroup', 'configuratorGroup.id = groupRelation.group_id')
            ->innerJoin('configuratorSet', 's_article_configurator_set_option_relations', 'optionRelation', 'optionRelation.set_id = configuratorSet.id')
            ->innerJoin('optionRelation', 's_article_configurator_options', 'configuratorOption', 'configuratorOption.id = optionRelation.option_id AND configuratorOption.group_id = configuratorGroup.id')
            ->leftJoin('configuratorGroup', 's_article_configurator_groups_attributes', 'configuratorGroupAttribute', 'configuratorGroupAttribute.groupID = configuratorGroup.id')
            ->leftJoin('configuratorOption', 's_article_configurator_options_attributes', 'configuratorOptionAttribute', 'configuratorOptionAttribute.optionID = configuratorOption.id')
            ->addOrderBy('configuratorGroup.position')
            ->addOrderBy('configuratorGroup.name')
            ->addOrderBy('configuratorOption.position')
            ->addOrderBy('configuratorOption.name')
            ->groupBy('configuratorOption.id');

        return $query;
    }
}
