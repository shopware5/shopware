<?php

namespace Shopware\Bundle\StoreFrontBundle\Struct;

/**
 * Interface ShopContextInterface
 * @package Shopware\Bundle\StoreFrontBundle\Struct
 */
interface ShopContextInterface
{
    /**
     * Returns the current active shop of the context.
     * The shop id is used as translation identifier.
     *
     * @return Shop
     */
    public function getShop();

    /**
     * @return Currency
     */
    public function getCurrency();

    /**
     * @return Customer\Group
     */
    public function getCurrentCustomerGroup();

    /**
     * @return Customer\Group
     */
    public function getFallbackCustomerGroup();

    /**
     * @return string
     */
    public function getBaseUrl();
}