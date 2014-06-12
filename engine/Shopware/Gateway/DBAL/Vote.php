<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;
use Shopware\Struct as Struct;

class Vote
{

    /**
     * @var \Shopware\Gateway\DBAL\Hydrator\Vote
     */
    private $voteHydrator;

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
     * @param Hydrator\Vote $voteHydrator
     */
    function __construct(
        ModelManager $entityManager,
        FieldHelper $fieldHelper,
        Hydrator\Vote $voteHydrator
    ) {
        $this->voteHydrator = $voteHydrator;
        $this->entityManager = $entityManager;
        $this->fieldHelper = $fieldHelper;
    }

    /**
     * @param Struct\ListProduct $product
     * @return Struct\Product\Vote
     */
    public function get(Struct\ListProduct $product)
    {
        $votes = $this->getList(array($product));

        return array_shift($votes);
    }

    /**
     * @param Struct\ListProduct[] $products
     * @return Struct\Product\Vote[]
     */
    public function getList(array $products)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->addSelect($this->fieldHelper->getVoteFields());

        $query->from('s_articles_vote', 'vote')
            ->where('vote.articleID IN (:ids)')
            ->orderBy('vote.articleID')
            ->addOrderBy('vote.datum', 'DESC')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $votes = array();
        foreach ($data as $row) {
            $id = $row['__vote_articleID'];
            $votes[$id][] = $this->voteHydrator->hydrate($row);
        }

        $result = array();
        foreach ($products as $product) {
            $number = $product->getNumber();
            $id = $product->getId();

            $result[$number] = $votes[$id];
        }

        return $result;
    }
}
