<?php

namespace Shopware\Bundle\CartBundle\Infrastructure\View;

use Shopware\Bundle\CartBundle\Domain\LineItem\CalculatedLineItemInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Media;

interface ViewLineItemInterface extends \JsonSerializable
{
    /**
     * @return CalculatedLineItemInterface
     */
    public function getLineItem();

    /**
     * @return string
     */
    public function getLabel();

    /**
     * @return Media|null
     */
    public function getCover();
}
