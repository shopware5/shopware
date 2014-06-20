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
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\Vote::get()
     *
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
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\VoteAverage::get()
     *
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
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\Vote::get()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     *
     * @return array Indexed by the product order number, each array element contains a \Shopware\Struct\Vote array.
     */
    public function getList(array $products, Struct\Context $context)
    {
        return $this->voteGateway->getList($products);
    }

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\VoteAverage::get()
     *
     * @param Struct\ListProduct[] $products
     * @param Struct\Context $context
     *
     * @return Struct\Product\VoteAverage[] Indexed by the product order number
     */
    public function getAverages(array $products, Struct\Context $context)
    {
        return $this->voteAverageGateway->getList($products);
    }

}
