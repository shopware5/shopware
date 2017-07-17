<?php

namespace StorefrontBundle\Struct;

use ProductBundle\Struct\ListingPrice;

class ListProduct extends \ProductBundle\Struct\ListProduct
{
    /**
     * @var ListingPrice
     */
    protected $listingPrice;

    public function getListingPrice(): ListingPrice
    {
        return $this->listingPrice;
    }

    public function setListingPrice(ListingPrice $listingPrice): void
    {
        $this->listingPrice = $listingPrice;
    }
}