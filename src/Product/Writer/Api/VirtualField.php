<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

class VirtualField extends Field
{
    /**
     * @var string
     */
    private $referencedFieldClass;

    /**
     * @param string $name
     * @param string $referencedFieldClass
     */
    public function __construct(string $name, string $referencedFieldClass)
    {
        parent::__construct($name);
        $this->referencedFieldClass = $referencedFieldClass;
    }

    /**
     * @return string
     */
    public function getReferencedFieldClass(): string
    {
        return $this->referencedFieldClass;
    }
}