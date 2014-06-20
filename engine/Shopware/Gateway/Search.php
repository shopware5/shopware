<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:13
 */
namespace Shopware\Gateway;

use Shopware\Gateway\Search\Criteria;
use Shopware\Gateway\Search\Result;
use Shopware\Struct\Context;


/**
 * @package Shopware\Gateway
 */
interface Search
{
    /**
     * Creates a product search result for the passed criteria object.
     * The criteria object contains different core conditions and plugin conditions.
     * This conditions has to be handled over the different condition handlers.
     *
     * The search gateway has to implement an event which plugin can be listened to,
     * to add their own handler classes.
     *
     * @param \Shopware\Gateway\Search\Criteria $criteria
     * @param Context $context
     * @return Result
     */
    public function search(Criteria $criteria, Context $context);
}