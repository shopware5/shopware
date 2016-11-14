<?php

namespace Shopware\Bundle\SearchBundleDBAL;

use Shopware\Bundle\SearchBundle\Criteria;
use Shopware\Bundle\SearchBundle\FacetInterface;
use Shopware\Bundle\SearchBundle\FacetResultInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

interface PartialFacetHandlerInterface
{
    /**
     * @param FacetInterface $facet
     * @param Criteria $reverted
     * @param Criteria $criteria
     * @param ShopContextInterface $context
     * @return FacetResultInterface
     */
    public function generatePartialFacet(
        FacetInterface $facet,
        Criteria $reverted,
        Criteria $criteria,
        ShopContextInterface $context
    );

    /**
     * Checks if the provided facet can be handled by this class.
     * @param FacetInterface $facet
     * @return bool
     */
    public function supportsFacet(FacetInterface $facet);
}
