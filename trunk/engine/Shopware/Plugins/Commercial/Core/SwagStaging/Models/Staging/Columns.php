<?php
namespace   Shopware\CustomModels\Staging;
use         Shopware\Components\Model\ModelEntity;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="s_plugin_staging_tables_columns")
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="Repository")
 * @Doctrine\ORM\Mapping\HasLifecycleCallbacks
 */
class Columns extends ModelEntity
{
    /**
     * @Doctrine\ORM\Mapping\Column(name="id", type="integer", nullable=false)
     * @Doctrine\ORM\Mapping\Id
     * @Doctrine\ORM\Mapping\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Doctrine\ORM\Mapping\Column(name="table_id", type="integer", nullable=false)
     */
    private $tableId;

    /**
     * @Doctrine\ORM\Mapping\Column(name="col", type="string", length=255, nullable=false)
     */
    private $column;

    public function getId()
    {
        return $this->id;
    }

    public function setColumn($column)
    {
        $this->column = $column;
    }

    public function getColumn()
    {
        return $this->column;
    }

    public function setTableId($tableId)
    {
        $this->tableId = $tableId;
    }

    public function getTableId()
    {
        return $this->tableId;
    }
}
