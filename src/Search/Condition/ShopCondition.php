<?php

namespace Shopware\Search\Condition;

use Shopware\Search\ConditionInterface;

class ShopCondition implements ConditionInterface
{
    /**
     * @var int[]
     */
    protected $ids;

    public function __construct(array $ids)
    {
        $this->ids = array_map(function($id) {
            return (int) $id;
        }, $ids);
    }

    public function getIds(): array
    {
        return $this->ids;
    }

    public function getName(): string
    {
        return self::class;
    }
}