<?php
/**
 * Created by PhpStorm.
 * User: oliverdenter
 * Date: 20.06.14
 * Time: 09:57
 */
namespace Shopware\Service;

use Shopware\Gateway;
use Shopware\Service\Core\Context;

interface Search
{
    /**
     * Creates a search request on the internal search gateway to
     * get the product result for the passed criteria object.
     *
     * @param Gateway\Search\Criteria $criteria
     * @param Context $context
     * @return Gateway\Search\Result
     */
    public function search(Gateway\Search\Criteria $criteria, Context $context);
}
