<?php

namespace Shopware\Gateway\DBAL;

use Shopware\Struct\Customer\Group;

class SearchPriceHelper
{
    public function getPriceSelection(Group $customerGroup)
    {
        $calculation = "(prices.price * variants.minpurchase)";

        if ($customerGroup->displayGrossPrices()) {
            $calculation .= " * (tax.tax + 100) / 100";
        }

        if ($customerGroup->useDiscount()) {
            $discount = (100 - (float) $customerGroup->getPercentageDiscount()) / 100;
            $calculation .= " * " . $discount;
        }

        return "(" . $calculation . ")";
    }
}