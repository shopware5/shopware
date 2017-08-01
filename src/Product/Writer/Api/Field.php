<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

abstract class Field
{
    /**
     * @var string
     */
    private $name;
    /**
     * @var array
     */
    private $insertConstraints;
    /**
     * @var array
     */
    private $updateConstraints;
    /**
     * @var string
     */
    private $storageName;

    public function __construct(string $name, string $storageName, array $insertConstraints = [], array $updateConstraints = [])
    {
        $this->name = $name;
        $this->storageName = $storageName;
        $this->insertConstraints = $insertConstraints;
        $this->updateConstraints = $updateConstraints;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return array
     */
    public function getInsertConstraints(): array
    {
        return $this->insertConstraints;
    }

    /**
     * @return array
     */
    public function getUpdateConstraints(): array
    {
        return $this->updateConstraints;
    }

    public function getFilters()
    {
        return [];
    }

    public function getValueTransformer()
    {
        return new ValueTransformerNoOp();
    }

    /**
     * @return string
     */
    public function getStorageName(): string
    {
        return $this->storageName;
    }
}



