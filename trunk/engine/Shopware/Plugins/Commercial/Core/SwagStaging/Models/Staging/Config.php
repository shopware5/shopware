<?php
namespace   Shopware\CustomModels\Staging;
use         Shopware\Components\Model\ModelEntity;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="s_plugin_staging_config")
 * @Doctrine\ORM\Mapping\HasLifecycleCallbacks
 */
class Config extends ModelEntity
{
    /**
     * @Doctrine\ORM\Mapping\Column(name="id", type="integer", nullable=false)
     * @Doctrine\ORM\Mapping\Id
     * @Doctrine\ORM\Mapping\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Doctrine\ORM\Mapping\Column(name="database_user", type="string", length=255, nullable=false)
     */
    private $databaseUser;

    public function getId()
    {
        return $this->id;
    }

    public function setDatabaseUser($databaseUser)
    {
        $this->databaseUser = $databaseUser;
    }

    public function getDatabaseUser()
    {
        return $this->databaseUser;
    }
}
