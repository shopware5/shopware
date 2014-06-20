<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 18.06.14
 * Time: 14:45
 */
namespace Shopware\Gateway;

use Shopware\Struct;


/**
 * @package Shopware\Gateway\DBAL
 */
interface Configurator
{
    /**
     * The \Shopware\Struct\Configurator\Set requires the following data:
     * - Configurator set
     * - Core attribute of the configurator set
     * - Groups of the configurator set
     * - Options of each group
     *
     * Required translation in the provided context language:
     * - Configurator groups
     * - Configurator options
     *
     * A selection can be provided to filter only possible configurations for the product.
     *
     * The last group of the selection contains all available options compatible with previous group selection.
     * Other groups only have the selected option.
     *
     * Example:
     *
     * The product contains the following configuration: (id)
     * - (9)  Gender: male(1), female(2)
     * - (10) Color:  white(3), green(4)
     * - (11) Size:   XL(5), L(6)
     *
     * The following variants can be ordered:
     *  - male   / green / XL
     *  - male   / white / L
     *  - female / white / XL
     *
     * First scenario:
     * The $selection parameter contains nothing:
     *
     *  - Gender:  male, female
     *  - Color:   white, green
     *  - Size:    XL, L
     * => All available options.
     *
     * Second scenario:
     * The $selection contains array(9 => 1) equals to (male)
     *
     *  - Gender:  male, female  (both available to revert the last selection)
     *  - Color:   white, green
     *  - Size:    XL, L
     *
     * Third scenario:
     * The $selection contains array(9 => 1, 10 => 3) equals to (male, white)
     *
     *  - Gender: male
     *  - Color:  white, green (both available to revert the last selection)
     *  - Size:   L
     *
     * @param Struct\ListProduct $product
     * @param Struct\Context $context
     * @param array $selection Indexed by the configurator group id and contains the option id as value
     * @return Struct\Configurator\Set
     */
    public function get(Struct\ListProduct $product, Struct\Context $context, array $selection);
}