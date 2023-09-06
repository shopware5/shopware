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

namespace Shopware\Bundle\StoreFrontBundle\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use PDO;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\ConfiguratorHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\ProductConfigurationGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ProductConfigurationGateway implements ProductConfigurationGatewayInterface
{
    private ConfiguratorHydrator $configuratorHydrator;

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
     */
    private FieldHelper $fieldHelper;

    private Connection $connection;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        ConfiguratorHydrator $configuratorHydrator
    ) {
        $this->connection = $connection;
        $this->configuratorHydrator = $configuratorHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * {@inheritdoc}
     */
    public function get(BaseProduct $product, ShopContextInterface $context)
    {
        $groups = $this->getList([$product], $context);

        return array_shift($groups);
    }

    /**
     * {@inheritdoc}
     */
    public function getList($products, ShopContextInterface $context)
    {
        if (empty($products)) {
            return [];
        }

        $ids = [];
        foreach ($products as $product) {
            $ids[] = $product->getVariantId();
        }
        $ids = array_unique($ids);

        $data = $this->getQuery($ids, $context)->execute()->fetchAll(PDO::FETCH_GROUP);

        $result = [];
        foreach ($data as $key => $groups) {
            $result[$key] = $this->configuratorHydrator->hydrateGroups($groups);
        }

        return $result;
    }

    /**
     * @param int[] $ids
     */
    private function getQuery(array $ids, ShopContextInterface $context): QueryBuilder
    {
        $query = $this->connection->createQueryBuilder();

        $query->select('variants.ordernumber as number')
            ->addSelect($this->fieldHelper->getConfiguratorGroupFields())
            ->addSelect($this->fieldHelper->getConfiguratorOptionFields());

        $query->from('s_article_configurator_option_relations', 'relations')
            ->innerJoin('relations', 's_articles_details', 'variants', 'variants.id = relations.article_id')
            ->innerJoin('relations', 's_article_configurator_options', 'configuratorOption', 'configuratorOption.id = relations.option_id')
            ->innerJoin('configuratorOption', 's_article_configurator_groups', 'configuratorGroup', 'configuratorGroup.id = configuratorOption.group_id')
            ->leftJoin('configuratorGroup', 's_article_configurator_groups_attributes', 'configuratorGroupAttribute', 'configuratorGroupAttribute.groupID = configuratorGroup.id')
            ->leftJoin('configuratorOption', 's_article_configurator_options_attributes', 'configuratorOptionAttribute', 'configuratorOptionAttribute.optionID = configuratorOption.id')
            ->where('relations.article_id IN (:ids)')
            ->addOrderBy('configuratorGroup.position')
            ->addOrderBy('configuratorGroup.id')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        $this->fieldHelper->addConfiguratorGroupTranslation($query, $context);
        $this->fieldHelper->addConfiguratorOptionTranslation($query, $context);

        return $query;
    }
}
