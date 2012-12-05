<?php
namespace   Shopware\CustomModels\Staging;
use         Shopware\Components\Model\ModelEntity;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="s_plugin_staging_jobs")
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="Repository")
 * @Doctrine\ORM\Mapping\HasLifecycleCallbacks
 */
class Jobs extends ModelEntity
{
    /**
     * @Doctrine\ORM\Mapping\Column(name="id", type="integer", nullable=false)
     * @Doctrine\ORM\Mapping\Id
     * @Doctrine\ORM\Mapping\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Doctrine\ORM\Mapping\Column(name="profile_id", type="integer",  nullable=false)
    */
    private $profileId;

    /**
     * @Doctrine\ORM\Mapping\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * @var \DateTime $createDate
     * @Doctrine\ORM\Mapping\Column(name="create_date", type="datetime", nullable=true)
     */
    private $createDate;

    /**
     * @var \DateTime $startDate
     * @Doctrine\ORM\Mapping\Column(name="start_date", type="datetime", nullable=true)
     */
    private $startDate;

    /**
     * @var \DateTime $endDate
     * @Doctrine\ORM\Mapping\Column(name="end_date", type="datetime", nullable=true)
     */
    private $endDate;

    /**
     * @Doctrine\ORM\Mapping\Column(name="user", type="string", length=255, nullable=false)
     */
    private $user;

    /**
     * @Doctrine\ORM\Mapping\Column(name="running", type="integer",  nullable=false)
     */
    private $running;

    /**
    * @Doctrine\ORM\Mapping\Column(name="jobs_total", type="integer",  nullable=false)
    */
    private $jobsTotal;

    /**
    * @Doctrine\ORM\Mapping\Column(name="jobs_current", type="integer",  nullable=false)
    */
    private $jobsCurrent;

    /**
    * @Doctrine\ORM\Mapping\Column(name="successful", type="integer",  nullable=false)
    */
    private $successful;

    /**
    * @Doctrine\ORM\Mapping\Column(name="error_msg", type="text",  nullable=false)
    */
    private $errorMsg;


    public function getId()
    {
        return $this->id;
    }

    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    public function getCreateDate()
    {
        return $this->createDate;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function setErrorMsg($errorMsg)
    {
        $this->errorMsg = $errorMsg;
    }

    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    public function setJobsCurrent($jobsCurrent)
    {
        $this->jobsCurrent = $jobsCurrent;
    }

    public function getJobsCurrent()
    {
        return $this->jobsCurrent;
    }

    public function setJobsTotal($jobsTotal)
    {
        $this->jobsTotal = $jobsTotal;
    }

    public function getJobsTotal()
    {
        return $this->jobsTotal;
    }

    public function setRunning($running)
    {
        $this->running = $running;
    }

    public function getRunning()
    {
        return $this->running;
    }

    public function setSuccessful($successful)
    {
        $this->successful = $successful;
    }

    public function getSuccessful()
    {
        return $this->successful;
    }

    public function setUser($user)
    {
        $this->user = $user;
    }

    public function getUser()
    {
        return $this->user;
    }

    /**
     * @param \DateTime $endDate
     */
    public function setEndDate($endDate)
    {
        $this->endDate = $endDate;
    }

    /**
     * @return \DateTime
     */
    public function getEndDate()
    {
        return $this->endDate;
    }

    /**
     * @param \DateTime $startDate
     */
    public function setStartDate($startDate)
    {
        $this->startDate = $startDate;
    }

    /**
     * @return \DateTime
     */
    public function getStartDate()
    {
        return $this->startDate;
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
