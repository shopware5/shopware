<?php
namespace   Shopware\CustomModels\Staging;
use         Shopware\Components\Model\ModelEntity;

/**
 * @Doctrine\ORM\Mapping\Entity
 * @Doctrine\ORM\Mapping\Table(name="s_plugin_staging_jobs_queue")
 * @Doctrine\ORM\Mapping\Entity(repositoryClass="Repository")
 * @Doctrine\ORM\Mapping\HasLifecycleCallbacks
 */
class Queue extends ModelEntity
{
    /**
     * @Doctrine\ORM\Mapping\Column(name="id", type="integer", nullable=false)
     * @Doctrine\ORM\Mapping\Id
     * @Doctrine\ORM\Mapping\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @Doctrine\ORM\Mapping\Column(name="job_id", type="integer", nullable=false)
     */
    private $jobId;

    /**
     * @Doctrine\ORM\Mapping\Column(name="text", type="string", length=255, nullable=false)
     */
    private $text;

    /**
     * @Doctrine\ORM\Mapping\Column(name="job", type="text",  nullable=false)
     */
    private $job;

    /**
     * @Doctrine\ORM\Mapping\Column(name="done", type="integer", nullable=false)
     */
    private $done;

    /**
     * @Doctrine\ORM\Mapping\Column(name="error_msg", type="string", length=255, nullable=false)
     */
    private $errorMsg;

    /**
     * @Doctrine\ORM\Mapping\Column(name="start", type="datetime", nullable=false)
     */
    private $start;

    /**
     * @Doctrine\ORM\Mapping\Column(name="duration", type="time", nullable=false)
     */
    private $duration;


    public function getId()
    {
        return $this->id;
    }

    public function setDone($done)
    {
        $this->done = $done;
    }

    public function getDone()
    {
        return $this->done;
    }

    public function setDuration($duration)
    {
        $this->duration = $duration;
    }

    public function getDuration()
    {
        return $this->duration;
    }

    public function setErrorMsg($errorMsg)
    {
        $this->errorMsg = $errorMsg;
    }

    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    public function setJob($job)
    {
        $this->job = $job;
    }

    public function getJob()
    {
        return $this->job;
    }

    public function setJobId($jobId)
    {
        $this->jobId = $jobId;
    }

    public function getJobId()
    {
        return $this->jobId;
    }

    public function setStart($start)
    {
        $this->start = $start;
    }

    public function getStart()
    {
        return $this->start;
    }

    public function setText($text)
    {
        $this->text = $text;
    }

    public function getText()
    {
        return $this->text;
    }
}
