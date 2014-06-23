<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

/**
 * @package Shopware\Gateway\DBAL
 */
class Configurator implements \Shopware\Gateway\Configurator
{
    /**
     * @var Hydrator\Configurator
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
     * @param ModelManager $entityManager
     * @param FieldHelper $fieldHelper
     * @param Hydrator\Configurator $configuratorHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\Configurator $configuratorHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->configuratorHydrator = $configuratorHydrator;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @inheritdoc
     */
    public function get(Struct\ListProduct $product, Struct\Context $context, array $selection)
    {
        $query = $this->getQuery();

        $query->select('products.id as arrayKey')
            ->addSelect($this->fieldHelper->getConfiguratorSetFields())
            ->addSelect($this->fieldHelper->getConfiguratorGroupFields())
            ->addSelect($this->fieldHelper->getConfiguratorOptionFields())
        ;

        $this->addSelectionCondition($query, $selection);

        $this->fieldHelper->addConfiguratorTranslation(
            $query,
            $context
        );

        $query->where('products.id = :id')
            ->setParameter(':language', $context->getShop()->getId())
            ->setParameter(':id', $product->getId());

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $this->configuratorHydrator->hydrate($data, $selection);
    }

    private function addSelectionCondition(QueryBuilder $query, array $selection)
    {
        $previous = null;
        foreach ($selection as $groupId => $optionId) {
            $tableAlias = 'group_table_' . (int) $groupId;
            $optionFilter = 'option_id_' . (int) $optionId;
            $groupFilter = 'group_' . (int) $groupId;
            $selectAlias = 'groupFilter_' . (int) $groupId;

            $query->addSelect(
                'GROUP_CONCAT(' . $tableAlias . '.article_id) as ' . $selectAlias
            );

            if ($previous) {
                $previous .= $tableAlias . '.article_id';
            }

            $query->leftJoin(
                'variants',
                's_article_configurator_option_relations',
                $tableAlias,
                $tableAlias . '.option_id = :' . $optionFilter .
                ' AND variants.id = ' . $tableAlias . '.article_id' . $previous
            );

            $previous .= ' AND ' . $tableAlias . '.article_id = ';

            $query->andHaving(
                '(' . $selectAlias . ' IS NOT NULL OR configuratorGroup.id = :' . $groupFilter . ')'
            );

            $query->setParameter(':' . $optionFilter, (int) $optionId)
                ->setParameter(':' . $groupFilter, (int) $groupId);
        }
    }

    private function getQuery()
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->from(
            's_article_configurator_sets',
            'configuratorSet'
        );

        $query->innerJoin(
            'configuratorSet',
            's_articles',
            'products',
            'products.configurator_set_id = configuratorSet.id'
        );

        $query->innerJoin(
            'configuratorSet',
            's_article_configurator_set_group_relations',
            'groupRelation',
            'groupRelation.set_id = configuratorSet.id'
        );

        $query->innerJoin(
            'groupRelation',
            's_article_configurator_groups',
            'configuratorGroup',
            'configuratorGroup.id = groupRelation.group_id'
        );

        $query->innerJoin(
            'configuratorSet',
            's_article_configurator_set_option_relations',
            'optionRelation',
            'optionRelation.set_id = configuratorSet.id'
        );

        $query->innerJoin(
            'optionRelation',
            's_article_configurator_options',
            'configuratorOption',
            'configuratorOption.id = optionRelation.option_id
             AND
             configuratorOption.group_id = configuratorGroup.id'
        );

        $query->innerJoin(
            'configuratorOption',
            's_article_configurator_option_relations',
            'variantOptions',
            'variantOptions.option_id = configuratorOption.id'
        );

        $query->innerJoin(
            'variantOptions',
            's_articles_details',
            'variants',
            'variants.id = variantOptions.article_id
             AND variants.active = 1
             AND variants.articleID = products.id
             AND (products.laststock * variants.instock) >= (products.laststock * variants.minpurchase)'
        );

        $query->addOrderBy('configuratorGroup.position')
            ->addOrderBy('configuratorGroup.name')
            ->addOrderBy('configuratorOption.position')
            ->addOrderBy('configuratorOption.name');


        $query->groupBy('configuratorOption.id');

        return $query;
    }

}
