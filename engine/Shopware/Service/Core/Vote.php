<?php

namespace Shopware\Service\Core;

use Shopware\Struct;
use Shopware\Service;
use Shopware\Gateway;

class Vote implements Service\Vote
{
    /**
     * @var Gateway\Vote
     */
    private $voteGateway;

    /**
     * @var Gateway\VoteAverage
     */
    private $voteAverageGateway;

    /**
     * @param Gateway\Vote $voteGateway
     * @param Gateway\VoteAverage $voteAverageGateway
     */
    function __construct(
        Gateway\Vote $voteGateway,
        Gateway\VoteAverage $voteAverageGateway
    ) {
        $this->voteGateway = $voteGateway;
        $this->voteAverageGateway = $voteAverageGateway;
    }

    /**
     * @inheritdoc
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $votes = $this->getList(array($product), $context);
        return array_shift($votes);
    }

    /**
     * @inheritdoc
     */
    public function getAverage(Struct\ListProduct $product, Struct\Context $context)
    {
        $average = $this->getAverages(array($product), $context);
        return array_shift($average);
    }

    /**
     * @inheritdoc
     */
    public function getList(array $products, Struct\Context $context)
    {
        return $this->voteGateway->getList($products);
    }

    /**
     * @inheritdoc
     */
    public function getAverages(array $products, Struct\Context $context)
    {
        return $this->voteAverageGateway->getList($products);
    }

}
