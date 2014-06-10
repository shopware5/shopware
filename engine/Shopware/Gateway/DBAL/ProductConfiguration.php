<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

class ProductConfiguration
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
            ->addSelect($this->fieldHelper->getConfiguratorGroupFields())
            ->addSelect($this->fieldHelper->getConfiguratorOptionFields())
        ;

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
            'configuratorOption',
            'configuratorOption.id = relations.option_id'
        );

        $query->innerJoin(
            'configuratorOption',
            's_article_configurator_groups',
            'configuratorGroup',
            'configuratorGroup.id = configuratorOption.group_id'
        );

        return $query;
    }
}