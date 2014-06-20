<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:52
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface Tax
{
    /**
     * The \Shopware\Struct\Tax requires the following data:
     * - Tax rule data
     *
     * Required conditions for the selection:
     * - The tax rule is selected according to the following criteria
     *  - Customer group
     *  - Area
     *  - Country
     *  - State
     * - The above rules are prioritized, from first to last.
     *
     * @param \Shopware\Struct\Customer\Group $customerGroup
     * @param \Shopware\Struct\Country\Area $area
     * @param \Shopware\Struct\Country $country
     * @param \Shopware\Struct\Country\State $state
     * @return Struct\Tax[] Indexed by 'tax_' + id
     */
    public function getRules(
        Struct\Customer\Group $customerGroup,
        Struct\Country\Area $area = null,
        Struct\Country $country = null,
        Struct\Country\State $state = null
    );
}