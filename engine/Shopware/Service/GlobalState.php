<?php

namespace Shopware\Service;

use Shopware\Struct as Struct;

/**
 * @package Shopware\Service
 */
class GlobalState
{
    private $globalStateHydrator;

    private $taxHydrator;

    private $customerGroupHydrator;

    private $currencyHydrator;

    private $session;

    function __construct(
        $currencyHydrator,
        $customerGroupHydrator,
        $globalStateHydrator,
        $taxHydrator,
        $session
    ) {
        $this->currencyHydrator = $currencyHydrator;
        $this->customerGroupHydrator = $customerGroupHydrator;
        $this->globalStateHydrator = $globalStateHydrator;
        $this->taxHydrator = $taxHydrator;
        $this->session = $session;
    }

    /**
     * @return Struct\GlobalState
     */
    public function get()
    {
        $state = new Struct\GlobalState();

        $state->setShop(new Struct\Shop());
        $state->getShop()->setId(1);

        $state->setCurrentCustomerGroup(
            Shopware()->Container()->get('customer_group_gateway_dbal')->getByKey('EK')
        );

        $state->setFallbackCustomerGroup(
            Shopware()->Container()->get('customer_group_gateway_dbal')->getByKey('EK')
        );

        $tax = Shopware()->Container()->get('tax_hydrator_dbal')->hydrate(
            Shopware()->Db()->fetchRow("SELECT * FROM s_core_tax WHERE id = 1")
        );

        $state->setTax($tax);

        $state->setCurrency(new Struct\Currency());
        $state->getCurrency()->setFactor(1);

        return $state;
    }
}