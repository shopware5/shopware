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

        $query->select(
            array(
                'COUNT(id) as total',
                'points'
            )
        );

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

        $query->select($this->getVoteFields())
            ->addSelect('variant.ordernumber as number');

        $query->from('s_articles_vote', 'votes')
            ->innerJoin(
                'votes',
                's_articles_details',
                'variant',
                'variant.articleID = votes.articleID AND variant.kind = 1'
            )
            ->where('votes.articleID IN (:ids)')
            ->orderBy('votes.articleID')
            ->addOrderBy('votes.datum', 'DESC')
            ->setParameter(':ids', $ids, Connection::PARAM_INT_ARRAY);

        /**@var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_ASSOC);

        $votes = array();
        foreach($data as $row) {
            $number = $row['number'];

            $votes[$number][] = $this->voteHydrator->hydrate($row);
        }

        return $votes;
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