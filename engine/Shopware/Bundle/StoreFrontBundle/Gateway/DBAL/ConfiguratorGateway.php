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
use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware_Components_Config;

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
     * @var Shopware_Components_Config
     */
    private $shopwareConfig;

    /**
     * @param Connection                                                      $connection
     * @param FieldHelper                                                     $fieldHelper
     * @param Hydrator\ConfiguratorHydrator                                   $configuratorHydrator
     * @param \Shopware\Bundle\StoreFrontBundle\Gateway\MediaGatewayInterface $mediaGateway
     * @param Shopware_Components_Config                                      $shopwareConfig
     */
    public function __construct(
        Connection $connection,
        FieldHelper $fieldHelper,
        Hydrator\ConfiguratorHydrator $configuratorHydrator,
        Gateway\MediaGatewayInterface $mediaGateway,
        Shopware_Components_Config $shopwareConfig
    ) {
        $this->connection = $connection;
        $this->configuratorHydrator = $configuratorHydrator;
        $this->fieldHelper = $fieldHelper;
        $this->mediaGateway = $mediaGateway;
        $this->shopwareConfig = $shopwareConfig;
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
    public function getProductCombinations(Struct\BaseProduct $product, array $currentSelection)
    {
        $queryBuilder = $this->getQuery();
        $queryBuilder->select(
                'otherConfiguratorGroup.id AS groupId',
                'includedOption.id AS optionId'
            )
            // load additional groups from same configurator set
            ->innerJoin(
                'configuratorSet',
                's_article_configurator_set_group_relations',
                'otherGroupRelation',
                $queryBuilder->expr()->eq('otherGroupRelation.set_id', 'configuratorSet.id')
            )
            ->innerJoin(
                'otherGroupRelation',
                's_article_configurator_groups',
                'otherConfiguratorGroup',
                $queryBuilder->expr()->eq('otherGroupRelation.group_id', 'otherConfiguratorGroup.id')
            )
            // load options to additional groups from the same configurator set
            ->innerJoin(
                'configuratorOption',
                's_article_configurator_set_option_relations',
                'alternateOptionRelation',
                $queryBuilder->expr()->eq('alternateOptionRelation.option_id', 'configuratorOption.id')
            )
            ->innerJoin(
                'alternateOptionRelation',
                's_article_configurator_set_group_relations',
                'alternateGroupRelation',
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('alternateGroupRelation.set_id', 'alternateOptionRelation.set_id'),
                    $queryBuilder->expr()->eq('alternateGroupRelation.group_id', 'configuratorOption.group_id')
                )
            )
            // load additional options from the same group except the selected one
            ->leftJoin(
                'configuratorOption',
                's_article_configurator_options',
                'alternateOption',
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('alternateOption.group_id', 'configuratorOption.group_id'),
                    $queryBuilder->expr()->neq('alternateOption.id', 'configuratorOption.id')
                )
            )
            ->innerJoin(
                'alternateGroupRelation',
                's_article_configurator_set_option_relations',
                'veryAlternateOptionRelation',
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('veryAlternateOptionRelation.set_id', 'alternateGroupRelation.set_id '),
                    $queryBuilder->expr()->eq('veryAlternateOptionRelation.option_id', 'alternateOption.id')
                )
            )
            // map alternative values to chosen values
            ->innerJoin(
                'groupRelation',
                's_article_configurator_set_group_relations',
                'includedGroupRelation',
                $queryBuilder->expr()->eq('includedGroupRelation.set_id', 'groupRelation.set_id')
            )
            ->innerJoin(
                'includedGroupRelation',
                's_article_configurator_set_option_relations',
                'includedOptionRelation',
                $queryBuilder->expr()->eq('includedGroupRelation.set_id', 'includedOptionRelation.set_id')
            )
            ->innerJoin(
                'includedOptionRelation',
                's_article_configurator_options',
                'includedOption',
                $queryBuilder->expr()->eq('includedOptionRelation.option_id', 'includedOption.id')
            )
            ->where(
                $queryBuilder->expr()->eq('products.id', ':productId'),
                $queryBuilder->expr()->eq('includedOption.group_id', 'otherConfiguratorGroup.id')
            )
            ->setParameter('productId', $product->getId())
            ->groupBy(
                'otherConfiguratorGroup.id',
                'includedOption.id'
            )
        ;

        if (!empty($currentSelection)) {
            $queryBuilder->andWhere(
                    $queryBuilder->expr()->in('configuratorOption.id', ':selection'),
                    $queryBuilder->expr()->notIn('includedOption.id', ':selection')
                )
                ->setParameter('selection', $currentSelection, Connection::PARAM_INT_ARRAY)
            ;
        }

        $possibleSelections = [];
        $rows = $queryBuilder->execute()->fetchAll(\PDO::FETCH_ASSOC);

        foreach ($rows as $alternation) {
            $groupId = (int) $alternation['groupId'];
            $alternateOptionId = (int) $alternation['optionId'];

            $possibleSelection = $currentSelection;
            $possibleSelection[$groupId] = $alternateOptionId;

            if ($this->selectionHasVariants($product, $possibleSelection)) {
                $possibleSelections[] = $possibleSelection;
            }
        }

        return $possibleSelections;
    }

    /**
     * @param Struct\BaseProduct $product
     * @param int[]              $possibleSelection
     *
     * @return bool
     */
    protected function selectionHasVariants(Struct\BaseProduct $product, $possibleSelection)
    {
        $queryBuilder = $this->connection->createQueryBuilder();

        $queryBuilder->from('s_articles_details', 'variants')
            ->select(
                'variants.id AS id',
                'COUNT(1) AS relationCount'
            )
            ->innerJoin(
                'variants',
                's_article_configurator_option_relations',
                'relation',
                $queryBuilder->expr()->eq('relation.article_id', 'variants.id')
            )
            ->where(
                $queryBuilder->expr()->eq('variants.articleID', ':productId'),
                $queryBuilder->expr()->in('relation.option_id', ':selection'),
                'variants.active'
            )
            ->groupBy('variants.id')
            ->having($queryBuilder->expr()->eq('relationCount', ':count'))
            ->setParameter('productId', $product->getId())
            ->setParameter('selection', $possibleSelection, Connection::PARAM_INT_ARRAY)
            ->setParameter('count', count($possibleSelection))
        ;

        if ($this->shopwareConfig->get('hideNoInstock')) {
            $queryBuilder->andWhere(
                $queryBuilder->expr()->gte(
                    'variants.laststock * variants.instock',
                    'variants.laststock * variants.minpurchase'
                )
            );
        }

        return $queryBuilder->execute()->fetchColumn() !== false;
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
