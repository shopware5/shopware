<?php

namespace ProductBundle\Gateway\Aggregator;

use Doctrine\DBAL\Connection;
use ProductBundle\Struct\VoteAverageCollection;
use Shopware\Bundle\StoreFrontBundle\Context\TranslationContext;
use Shopware\Bundle\StoreFrontBundle\Vote\VoteHydrator;

class VoteAverageAggregator
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var VoteHydrator
     */
    private $voteHydrator;

    /**
     * @var \Shopware_Components_Config
     */
    private $config;

    public function read(array $productNumbers, TranslationContext $context): VoteAverageCollection
    {
        $query = $this->connection->createQueryBuilder();

        $query->select([
            'variant.ordernumber',
            'COUNT(vote.id) as total',
            'vote.points'
        ]);

        $query
            ->from('s_articles_vote', 'vote')
            ->andWhere('vote.articleID IN (:products)')
            ->andWhere('vote.active = 1')
            ->addGroupBy('vote.articleID')
            ->addGroupBy('vote.points')
            ->addOrderBy('vote.articleID', 'ASC')
            ->setParameter(':products', $productNumbers, Connection::PARAM_INT_ARRAY);

        if ($this->config->get('displayOnlySubShopVotes')) {
            $query->andWhere('(vote.shop_id = :shopId OR vote.shop_id IS NULL)');
            $query->setParameter(':shopId', $context->getShopId());
        }

        /** @var $statement \Doctrine\DBAL\Driver\ResultStatement */
        $statement = $query->execute();

        $data = $statement->fetchAll(\PDO::FETCH_GROUP);

        $result = new VoteAverageCollection();
        foreach ($data as $number => $votes) {
            $result->add($number, $this->voteHydrator->hydrateAverage($votes));
        }

        return $result;
    }
}