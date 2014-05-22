<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

class ProductConfiguration extends Gateway
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

    /**
     * @param Struct\ListProduct[] $products
     * @return Group
     */
    public function getList(array $products)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getVariantId();
        }

        $query = $this->getQuery()
            ->select('variants.ordernumber as number')
            ->addSelect($this->getGroupFields())
            ->addSelect($this->getOptionFields());

        $query->where('relations.article_id IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $result = array();
        foreach ($data as $key => $groups) {
            $result[$key] = $this->configuratorHydrator->hydrateGroups($groups);
        }

        return $result;
    }

    protected function getQuery()
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->from('s_article_configurator_option_relations', 'relations');

        $query->innerJoin(
            'relations',
            's_articles_details',
            'variants',
            'variants.id = relations.article_id'
        );

        $query->innerJoin(
            'relations',
            's_article_configurator_options',
            'options',
            'options.id = relations.option_id'
        );

        $query->innerJoin(
            'options',
            's_article_configurator_groups',
            'groups',
            'groups.id = options.group_id'
        );

        return $query;
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