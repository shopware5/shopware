<?php declare(strict_types=1);

namespace Shopware\Product\Writer\Api;

class WritableField extends Field
{

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

    /**
     * @var string
     */
    private $tableName;

    /**
     * @param string $name
     * @param string $storageName
     * @param string $tableName
     * @param array $insertConstraints
     * @param array $updateConstraints
     */
    public function __construct(string $name, string $storageName, string $tableName, array $insertConstraints = [], array $updateConstraints = [])
    {
        parent::__construct($name);
        $this->tableName = $tableName;
        $this->storageName = $storageName;
        $this->insertConstraints = $insertConstraints;
        $this->updateConstraints = $updateConstraints;
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

    /**
     * @return array
     */
    public function getFilters()
    {
        return [];
    }

    /**
     * @return ValueTransformerNoOp
     */
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

    /**
     * @return string
     */
    public function getTableName(): string
    {
        return $this->tableName;
    }


}