<?php

namespace Shopware\Service;

use Shopware\Gateway\DBAL as Gateway;
use Shopware\Struct;

class Vote
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
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Product\Vote[]
     */
    public function get(Struct\ListProduct $product, Struct\Context $context)
    {
        $votes = $this->getList(array($product), $context);
        return array_shift($votes);
    }

    /**
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @return Struct\Product\VoteAverage
     */
    public function getAverage(Struct\ListProduct $product, Struct\Context $context)
    {
        $average = $this->getAverages(array($product), $context);
        return array_shift($average);
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param \Shopware\Struct\Context $context
     *
     * @return array
     */
    public function getList(array $products, Struct\Context $context)
    {
        return $this->voteGateway->getList($products);
    }

    /**
     * @param Struct\ListProduct[] $products
     * @param \Shopware\Struct\Context $context
     *
     * @return Struct\Product\VoteAverage[]
     */
    public function getAverages(array $products, Struct\Context $context)
    {
        return $this->voteAverageGateway->getList($products);
    }

}
