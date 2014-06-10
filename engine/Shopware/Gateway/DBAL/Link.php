<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

class Link
{
    /**
     * @var Hydrator\Link
     */
    private $linkHydrator;

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
     * @param Hydrator\Link $linkHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\Link $linkHydrator
    ) {
        $this->entityManager = $entityManager;
        $this->fieldHelper = $fieldHelper;
        $this->linkHydrator = $linkHydrator;
    }

    /**
     * @param Struct\ListProduct[] $products
     * @return Struct\Product\Link[] An array which indexed with the product number, each array element contains
     * an additionally array of product link structs
     */
    public function getList(array $products)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select($this->fieldHelper->getLinkFields());

        $query->from('s_articles_information', 'link')
            ->leftJoin(
                'link',
                's_articles_information_attributes',
                'linkAttribute',
                'linkAttribute.informationID = link.id'
            );

        $query->where('link.articleID IN (:ids)')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $links = array();
        foreach ($data as $row) {
            $key = $row['articleID'];

            $link = $this->linkHydrator->hydrate($row);

            $links[$key][] = $link;
        }

        $result = array();
        foreach ($products as $product) {
            $result[$product->getNumber()] = $links[$product->getId()];
        }

        return $result;
    }
}
