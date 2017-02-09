<?php

namespace Shopware\Bundle\CartBundle\Infrastructure\View;

use Shopware\Bundle\CartBundle\Domain\Collection;

class ViewLineItemCollection extends Collection
{
    /**
     * @var ViewLineItemInterface[]
     */
    protected $items = [];

    /**
     * @param ViewLineItemInterface $item
     */
    public function add($item)
    {
        $this->items[$item->getLineItem()->getIdentifier()] = $item;
    }

    /**
     * @param string $identifier
     * @return bool
     */
    public function has($identifier)
    {
        return parent::has($identifier);
    }

    /**
     * @param string $identifier
     * @return null|ViewLineItemInterface
     */
    public function get($identifier)
    {
        return parent::get($identifier);
    }

    /**
     * @param string $identifier
     */
    public function remove($identifier)
    {
        return parent::remove($identifier);
    }

    /**
     * @return string[]
     */
    public function getIdentifiers()
    {
        return array_keys($this->items);
    }
}
