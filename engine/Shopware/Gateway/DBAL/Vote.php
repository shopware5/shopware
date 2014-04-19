<?php

namespace Shopware\Gateway\DBAL;

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
    )
    {
        $this->voteHydrator = $voteHydrator;
        $this->entityManager = $entityManager;
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
    public function getAverage(Struct\ListProduct $product)
    {
        $query = $this->entityManager->getDBALQueryBuilder();

        $query->select(array(
            'COUNT(id) as total',
            'points'
        ));

        $query->from('s_articles_vote', 'votes')
            ->where('votes.articleID = :product')
            ->andWhere('votes.active = 1')
            ->groupBy('votes.points')
            ->orderBy('votes.points', 'ASC')
            ->setParameter(':product', $product->getId());

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        return $this->voteHydrator->hydrateAverage($data);
    }

}