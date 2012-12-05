<?php
namespace   Shopware\CustomModels\Staging;
use         Shopware\Components\Model\ModelEntity;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="s_plugin_staging_tables")
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="Repository")
 * @Doctrine\ORM\Mapping\HasLifecycleCallbacks
 */
class Tables extends ModelEntity
{
    /**
     * @Doctrine\ORM\Mapping\Column(name="id", type="integer", nullable=false)
     * @Doctrine\ORM\Mapping\Id
     * @Doctrine\ORM\Mapping\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Doctrine\ORM\Mapping\Column(name="profile_id", type="integer", nullable=false)
     */
    private $profileId;

    /**
     * @Doctrine\ORM\Mapping\Column(name="table_name", type="string", length=255, nullable=false, unique=true)
     */
    private $tableName;

    /**
     * @Doctrine\ORM\Mapping\Column(name="strategy", type="string", length=255, nullable=false)
     */
    private $strategy;

    public function getId()
    {
        return $this->id;
    }

    public function setStrategy($strategy)
    {
        $this->strategy = $strategy;
    }

    public function getStrategy()
    {
        return $this->strategy;
    }

    public function setTableName($tableName)
    {
        $this->tableName = $tableName;
    }

    public function getTableName()
    {
        return $this->tableName;
    }

    public function setProfileId($profileId)
    {
        $this->profileId = $profileId;
    }

    public function getProfileId()
    {
        return $this->profileId;
    }
}
