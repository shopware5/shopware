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
use Shopware\Bundle\StoreFrontBundle\Gateway\ConfiguratorGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Gateway\DBAL\Hydrator\ConfiguratorHydrator;
use Shopware\Bundle\StoreFrontBundle\Gateway\MediaGatewayInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\BaseProduct;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class ConfiguratorGateway implements ConfiguratorGatewayInterface
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

    private MediaGatewayInterface $mediaGateway;

    private Connection $connection;

    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        ConfiguratorHydrator $configuratorHydrator,
        MediaGatewayInterface $mediaGateway
    ) {
        $this->connection = $connection;
        $this->configuratorHydrator = $configuratorHydrator;
        $this->fieldHelper = $fieldHelper;
        $this->mediaGateway = $mediaGateway;
    }

    /**
     * {@inheritdoc}
     */
    public function get(BaseProduct $product, ShopContextInterface $context)
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

        $data = $query->execute()->fetchAll(PDO::FETCH_ASSOC);

        return $this->configuratorHydrator->hydrate($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getConfiguratorMedia(BaseProduct $product, ShopContextInterface $context)
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

        $data = $query->execute()->fetchAll(PDO::FETCH_KEY_PAIR);
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
    public function getProductCombinations(BaseProduct $product)
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'relations.option_id',
            "GROUP_CONCAT(DISTINCT assignedRelations.option_id, '' SEPARATOR '|') as combinations",
        ]);

        $query->from('s_article_configurator_option_relations', 'relations')
            ->innerJoin('relations', 's_articles_details', 'variant', 'variant.id = relations.article_id AND variant.articleID = :articleId AND variant.active = 1')
            ->innerJoin(
                'variant',
                's_articles',
                'product',
                'product.id = variant.articleID AND (
                    (variant.laststock * variant.instock) >= (variant.laststock * variant.minpurchase)
                )'
            )
            ->leftJoin('relations', 's_article_configurator_option_relations', 'assignedRelations', 'assignedRelations.article_id = relations.article_id AND assignedRelations.option_id != relations.option_id')
            ->groupBy('relations.option_id')
            ->setParameter(':articleId', $product->getId());

        $data = $query->execute()->fetchAll(PDO::FETCH_KEY_PAIR);

        foreach ($data as &$row) {
            $row = explode('|', $row);
        }

        return $data;
    }

    public function getAvailableConfigurations(BaseProduct $product): array
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            "GROUP_CONCAT(DISTINCT relations.option_id, '' SEPARATOR '|') as combinations",
        ]);

        $query->from('s_articles_details', 'variant')
            ->innerJoin(
                'variant',
                's_articles',
                'product',
                'product.id = variant.articleID AND product.id = :productId AND (
                    (variant.laststock * variant.instock) >= (variant.laststock * variant.minpurchase)
                )'
            )
            ->leftJoin('variant', 's_article_configurator_option_relations', 'relations', 'variant.id = relations.article_id')
            ->where('variant.active = 1')
            ->groupBy('variant.id')
            ->setParameter(':productId', $product->getId());

        $data = $query->execute()->fetchAll(PDO::FETCH_COLUMN);

        $result = [];
        foreach ($data as $row) {
            if (empty($row)) {
                continue;
            }
            $rowIds = array_map('\intval', explode('|', $row));

            foreach ($rowIds as $optionId) {
                $result[$optionId][] = $rowIds;
            }
        }

        return $result;
    }

    private function getQuery(): QueryBuilder
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
