<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

class Link extends Gateway
{
    /**
     * @var Hydrator\Link
     */
    private $linkHydrator;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\Link $linkHydrator
     */
    function __construct(
        ModelManager $entityManager,
        Hydrator\Link $linkHydrator
    ) {
        $this->entityManager = $entityManager;
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

        $query->select($this->getLinkFields())
            ->addSelect($this->getTableFields('s_articles_information_attributes', 'linkAttribute'));

        $query->from('s_articles_information', 'links')
            ->leftJoin(
                'links',
                's_articles_information_attributes',
                'linkAttribute',
                'linkAttribute.informationID = links.id'
            );

        $query->where('links.articleID IN (:ids)')
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

    private function getLinkFields()
    {
        return array(
            'links.id',
            'links.articleID',
            'links.description',
            'links.link',
            'links.target'
        );
    }
}
