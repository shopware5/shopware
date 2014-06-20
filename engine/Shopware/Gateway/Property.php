<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:51
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface Property
{
    /**
     * The \Shopware\Struct\Property\Set requires the following data:
     * - Property set data
     * - Property groups data
     * - Property options data
     * - Core attribute of the property set
     *
     * Required translation in the provided context language:
     * - Property set
     * - Property groups
     * - Property options
     *
     * Required conditions for the selection:
     * - Selects only values which ids provided
     * - Property values has to be sorted by the \Shopware\Struct\Property\Set sort mode.
     *  - Sort mode equals to 1, the values are sorted by the numeric value
     *  - Sort mode equals to 3, the values are sorted by the position
     *  - In all other cases the values are sorted by their alphanumeric value
     *
     * @param array $valueIds
     * @param \Shopware\Struct\Context $context
     * @return Struct\Property\Set[] Each array element (set, group, option) is indexed by his id
     */
    public function getList(array $valueIds, Struct\Context $context);
}