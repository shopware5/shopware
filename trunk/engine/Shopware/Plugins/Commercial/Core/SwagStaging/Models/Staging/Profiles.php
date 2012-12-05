<?php
namespace   Shopware\CustomModels\Staging;
use         Shopware\Components\Model\ModelEntity;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="s_plugin_staging_jobs_profiles")
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="Repository")
 * @Doctrine\ORM\Mapping\HasLifecycleCallbacks
 */
class Profiles extends ModelEntity
{
    /**
     * @Doctrine\ORM\Mapping\Column(name="id", type="integer", nullable=false)
     * @Doctrine\ORM\Mapping\Id
     * @Doctrine\ORM\Mapping\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Doctrine\ORM\Mapping\Column(name="profile_assignment", type="string", length=255, nullable=false)
     */
    private $profileAssignment;

    /**
     * @Doctrine\ORM\Mapping\Column(name="profile_key", type="string", length=255, nullable=false)
     */
    private $profileKey;

    /**
     * @Doctrine\ORM\Mapping\Column(name="profile_text", type="string", length=255, nullable=false)
     */
    private $profileText;

    /**
     * @Doctrine\ORM\Mapping\Column(name="jobs_per_request", type="integer", nullable=false)
     */
    private $jobsPerRequest;

    public function setJobsPerRequest($jobsPerRequest)
    {
        $this->jobsPerRequest = $jobsPerRequest;
    }

    public function getJobsPerRequest()
    {
        return $this->jobsPerRequest;
    }

    /**
     * @Doctrine\ORM\Mapping\Column(name="profile_description", type="string", length=255, nullable=false)
     */
    private $profileDescription;

    public function getId()
    {
        return $this->id;
    }

    public function setProfileAssignment($profileAssignment)
    {
        $this->profileAssignment = $profileAssignment;
    }

    public function getProfileAssignment()
    {
        return $this->profileAssignment;
    }

    public function setProfileDescription($profileDescription)
    {
        $this->profileDescription = $profileDescription;
    }

    public function getProfileDescription()
    {
        return $this->profileDescription;
    }

    public function setProfileKey($profileKey)
    {
        $this->profileKey = $profileKey;
    }

    public function getProfileKey()
    {
        return $this->profileKey;
    }

    public function setProfileText($profileText)
    {
        $this->profileText = $profileText;
    }

    public function getProfileText()
    {
        return $this->profileText;
    }
}
