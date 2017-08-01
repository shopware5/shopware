<?php

namespace SearchBundle\Condition;

use Shopware\Bundle\SearchBundle\ConditionInterface;

class ShopCondition implements ConditionInterface
{
    /**
     * @var int[]
     */
    protected $ids;

    public function __construct(array $ids)
    {
        $this->ids = $ids;
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