<?php

namespace SearchBundle\Condition;

use Shopware\Bundle\SearchBundle\ConditionInterface;

class ActiveCondition implements ConditionInterface
{
    /**
     * @var boolean
     */
    protected $active;

    public function __construct(bool $active = true)
    {
        $this->active = $active;
    }

    public function isActive(): bool
    {
        return $this->active;
    }

    public function getName(): string
    {
        return self::class;
    }
}