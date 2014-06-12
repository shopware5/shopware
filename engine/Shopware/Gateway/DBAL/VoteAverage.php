<?php

namespace Shopware\Gateway\DBAL;

use Doctrine\DBAL\Connection;
use Shopware\Components\Model\ModelManager;
use Shopware\Gateway\DBAL\Hydrator;
use Shopware\Struct;

class VoteAverage
{
    /**
     * @var Hydrator\Vote
     */
    private $voteHydrator;

    /**
     * @param \Shopware\Components\Model\ModelManager $entityManager
     * @param Hydrator\Vote $voteHydrator
     */
    function __construct(ModelManager $entityManager, Hydrator\Vote $voteHydrator)
    {
        $this->entityManager = $entityManager;
        $this->voteHydrator = $voteHydrator;
    }

    /**
     * Selects the aggregated product vote meta information.
     * This data contains the total of the product votes,
     * the average value of the rating and the count of each
     * different point rating.
     *
     * @param Struct\ListProduct $product
     * @return \Shopware\Struct\Product\VoteAverage
     */
    public function get(Struct\ListProduct $product)
    {
        $votes = $this->getList(array($product));

        return array_shift($votes);
    }

    /**
     * @param Struct\ListProduct[] $products
     * @return \Shopware\Struct\Product\VoteAverage
     */
    public function getList(array $products)
    {
        $ids = array();
        foreach ($products as $product) {
            $ids[] = $product->getId();
        }

        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select(
            array(
                'articleID',
                'COUNT(id) as total',
                'points'
            )
        );

        $query->from('s_articles_vote', 'votes')
            ->where('votes.articleID IN (:products)')
            ->andWhere('votes.active = 1')
            ->groupBy('votes.articleID')
            ->addGroupBy('votes.points')
            ->orderBy('votes.articleID', 'ASC')
            ->addOrderBy('votes.points', 'ASC')
            ->setParameter(':products', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $result = array();
        foreach ($products as $product) {
            $key = $product->getNumber();

            $votes = $data[$product->getId()];

            $result[$key] = $this->voteHydrator->hydrateAverage($votes);
        }

        return $result;
    }
}
