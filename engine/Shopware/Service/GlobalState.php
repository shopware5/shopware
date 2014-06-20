<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:55
 */
namespace Shopware\Service;

use Shopware\Struct;


/**
 * @package Shopware\Service
 */
interface GlobalState
{
    /**
     * The \Shopware\Struct\Context requires the following data:
     * - Current shop
     * - Current language
     * - Current customer group
     * - Fallback customer group of the current shop
     * - Current country data (area, country, state)
     * - The currency of the shop
     * - Different tax rules for the current context
     *
     * Required conditions for the selection:
     * - Use the `shop` service of the di container for the language and current category
     * - Use the `session` service of the di container for the current user data.
     */
    public function initialize();

    /**
     * To get detailed information about the selection conditions, structure and content of the returned object,
     * please refer to the linked classes.
     *
     * @see \Shopware\Gateway\GlobaleState::initialize()
     *
     * @return Struct\Context
     */
    public function get();
}
