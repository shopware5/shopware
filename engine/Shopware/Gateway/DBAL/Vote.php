<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator as Hydrator;
use Shopware\Struct as Struct;

class Vote extends Gateway
{

    /**
     * @var \Shopware\Gateway\DBAL\Hydrator\Vote
     */
    private $voteHydrator;

    /**
     * @param ModelManager $entityManager
     * @param Hydrator\Vote $voteHydrator
     */
    function __construct(
        ModelManager $entityManager,
        Hydrator\Vote $voteHydrator
    ) {
        $this->voteHydrator = $voteHydrator;
        $this->entityManager = $entityManager;
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
        foreach($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->addSelect($this->getVoteFields());

        $query->from('s_articles_vote', 'votes')
            ->where('votes.articleID IN (:ids)')
            ->orderBy('votes.articleID')
            ->addOrderBy('votes.datum', 'DESC')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $votes = array();
        foreach($data as $row) {
            $id = $row['articleID'];
            $votes[$id][] = $this->voteHydrator->hydrate($row);
        }

        $result = array();
        foreach($products as $product) {
            $number = $product->getNumber();
            $id = $product->getId();

            $result[$number] = $votes[$id];
        }

        return $result;
    }

    private function getVoteFields()
    {
        return array(
            'votes.id',
            'votes.articleID',
            'votes.name',
            'votes.headline',
            'votes.comment',
            'votes.points',
            'votes.datum',
            'votes.active',
            'votes.email',
            'votes.answer',
            'votes.answer_date'
        );
    }

}