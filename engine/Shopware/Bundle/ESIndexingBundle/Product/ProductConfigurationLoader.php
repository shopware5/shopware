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

namespace Shopware\Bundle\ESIndexingBundle\Product;

use Doctrine\DBAL\Connection;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\FieldHelper;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\ConfiguratorHydrator;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware_Components_Config;

class ProductConfigurationLoader
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var ConfiguratorHydrator
     */
    private $hydrator;

    /**
     * @var FieldHelper
     */
    private $fieldHelper;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    public function __construct(
        Connection $connection,
        ConfiguratorHydrator $hydrator,
        FieldHelper $fieldHelper,
        Shopware_Components_Config $config
    ) {
        $this->connection = $connection;
        $this->hydrator = $hydrator;
        $this->fieldHelper = $fieldHelper;
        $this->config = $config;
    }

    /**
     * Get possible combinations of all products
     *
     * @return array
     */
    public function getCombinations(array $articleIds)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'variant.articleID',
            "GROUP_CONCAT(DISTINCT relations.option_id ORDER BY relations.option_id  SEPARATOR '-') as combinations",
        ]);

        $query->from('s_article_configurator_option_relations', 'relations');
        $query->innerJoin(
            'relations',
            's_articles_details',
            'variant',
            'variant.id = relations.article_id AND variant.articleId IN (:articleIds) AND variant.active = 1'
        );

        $query->innerJoin(
            'variant',
            's_articles',
            'product',
            'product.id = variant.articleID'
        );

        if ($this->config->get('hideNoInStock')) {
            $query->andWhere('(variant.laststock * variant.instock) >= (variant.laststock * variant.minpurchase)');
        }

        $query->addGroupBy('variant.articleID');
        $query->addGroupBy('variant.id');

        $query->setParameter(':articleIds', $articleIds, Connection::PARAM_STR_ARRAY);

        /** @var \Doctrine\DBAL\Driver\ResultStatement $statement */
        $statement = $query->execute();

        return $statement->fetchAll(\PDO::FETCH_GROUP | \PDO::FETCH_COLUMN);
    }

    /**
     * Fetches  all groups with all options for provided products
     *
     * @return array<int, array<\Shopware\Bundle\StoreFrontBundle\Struct\Configurator\Group>>
     */
    public function getConfigurations(array $articleIds, ShopContextInterface $context)
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

        $query->where('variant.articleId IN (:articleIds)');
        $query->setParameter(':articleIds', $articleIds, Connection::PARAM_STR_ARRAY);

        $data = $query->execute()->fetchAll(\PDO::FETCH_GROUP);

        $result = [];
        foreach ($data as $productId => $rows) {
            $result[(int) $productId] = $this->hydrator->hydrateGroups($rows);
        }

        return $result;
    }
}
