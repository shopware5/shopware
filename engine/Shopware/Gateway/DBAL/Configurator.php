<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Components\Model\DBAL\QueryBuilder;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

class Configurator extends Gateway
{
    /**
     * @var Hydrator\Configurator
     */
    private $configuratorHydrator;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\Configurator $configuratorHydrator
     */
    function __construct(
        ModelManager $entityManager,
        Hydrator\Configurator $configuratorHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->configuratorHydrator = $configuratorHydrator;
    }

    public function get(Struct\ListProduct $product, Struct\Context $context, array $selection)
    {
        $query = $this->getQuery();

        $query->select('products.id as arrayKey')
            ->addSelect($this->getSetFields())
            ->addSelect($this->getGroupFields())
            ->addSelect($this->getOptionFields());

        $this->addSelectionCondition($query, $selection);

        $query->where('products.id = :id')
            ->setParameter(':id', $product->getId());

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        Shopware()->Template()->assign('raw_data', $data);
        Shopware()->Template()->assign('sql', $query->getSQL());

        return $this->configuratorHydrator->hydrate($data, $selection);
    }

    private function addSelectionCondition(QueryBuilder $query, array $selection)
    {
        $previous = null;
        foreach ($selection as $groupId => $optionId) {
            $tableAlias = 'group_table_' . (int)$groupId;
            $optionFilter = 'option_id_' . (int)$optionId;
            $groupFilter = 'group_' . (int)$groupId;
            $selectAlias = 'groupFilter_' . (int)$groupId;

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
                '(' . $selectAlias . ' IS NOT NULL OR groups.id = :' . $groupFilter . ')'
            );

            $query->setParameter(':' . $optionFilter, (int)$optionId)
                ->setParameter(':' . $groupFilter, (int)$groupId);
        }


    }

    protected function getQuery()
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->from(
            's_article_configurator_sets',
            'sets'
        );

        $query->innerJoin(
            'sets',
            's_articles',
            'products',
            'products.configurator_set_id = sets.id'
        );

        $query->innerJoin(
            'sets',
            's_article_configurator_set_group_relations',
            'groupRelation',
            'groupRelation.set_id = sets.id'
        );

        $query->innerJoin(
            'groupRelation',
            's_article_configurator_groups',
            'groups',
            'groups.id = groupRelation.group_id'
        );

        $query->innerJoin(
            'sets',
            's_article_configurator_set_option_relations',
            'optionRelation',
            'optionRelation.set_id = sets.id'
        );

        $query->innerJoin(
            'optionRelation',
            's_article_configurator_options',
            'options',
            'options.id = optionRelation.option_id
             AND
             options.group_id = groups.id'
        );

        $query->innerJoin(
            'options',
            's_article_configurator_option_relations',
            'variantOptions',
            'variantOptions.option_id = options.id'
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

        $query->orderBy('options.group_id')
            ->groupBy('options.id');

        return $query;
    }

    private function getSetFields()
    {
        return array(
            'sets.id as __set_id',
            'sets.name as __set_name',
            'sets.type as __set_type'
        );
    }

    private function getGroupFields()
    {
        return array(
            'groups.id as __group_id',
            'groups.name as __group_name',
            'groups.description as __group_description',
            'groups.position as __group_position'
        );
    }

    private function getOptionFields()
    {
        return array(
            'options.id as __option_id',
            'options.name as __option_name',
            'options.position as __option_position'
        );
    }

}