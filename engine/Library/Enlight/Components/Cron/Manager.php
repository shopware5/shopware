<?php
/**
 * Enlight
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://enlight.de/license
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@shopware.de so we can send you a copy immediately.
 *
 * @category   Enlight
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

/**
 * Stores and executes all registered cron jobs.
 *
 * The Enlight_Components_Cron_Manager is responsible to store all registered cron jobs and
 * execute the cron jobs with the associated cron job arguments.
 *
 * @category   Enlight
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Cron_Manager
{
    /**
     * @var Enlight_Components_Cron_Adapter
     */
    protected $adapter;

    /**
     * @var Enlight_Event_EventManager
     */
    protected $eventManager;

    protected $eventArgsClass = 'Enlight_Components_Cron_EventArgs';

    /**
     * Constructor can be injected with a read / write adapter object
     *
     * @param Enlight_Components_Cron_Adapter $adapter
     * @param Enlight_Event_EventManager|null $eventManager
     * @param string                          $eventArgsClass
     *
     * @return Enlight_Components_Cron_Manager
     */
    public function __construct(
        Enlight_Components_Cron_Adapter $adapter,
        Enlight_Event_EventManager $eventManager,
        $eventArgsClass = null
    ) {
        $this->setAdapter($adapter);
        $this->setEventManager($eventManager);
        if ($eventArgsClass !== null) {
            $this->eventArgsClass = $eventArgsClass;
        }
    }

    /**
     * Sets the read / write adapter
     *
     * @param Enlight_Components_Cron_Adapter $adapter
     *
     * @return Enlight_Components_Cron_Manager
     */
    public function setAdapter(Enlight_Components_Cron_Adapter $adapter)
    {
        $this->adapter = $adapter;

        return $this;
    }

    /**
     * Returns the read / write adapter
     *
     * @return Enlight_Components_Cron_Adapter|null
     */
    public function getAdapter()
    {
        return $this->adapter;
    }

    /**
     * Sets an Event Manager. Needed to execute the cron
     *
     * @param Enlight_Event_EventManager|null $eventManager
     *
     * @return Enlight_Components_Cron_Manager
     */
    public function setEventManager(Enlight_Event_EventManager $eventManager = null)
    {
        $this->eventManager = $eventManager;

        return $this;
    }

    /**
     * Returns the value set by setEventManager()
     *
     * @return Enlight_Event_EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Deactivate a given Cron Job in the crontab
     *
     * @param Enlight_Components_Cron_Job $job
     *
     * @return Enlight_Components_Cron_Adapter
     */
    public function disableJob(Enlight_Components_Cron_Job $job)
    {
        $job->setActive(false);

        return $this->adapter->updateJob($job);
    }

    /**
     * Deactivate a given Cron Job
     *
     * @param \Enlight_Components_Cron_Job $job
     *
     * @throws Enlight_Exception
     *
     * @return Enlight_Components_Cron_Manager
     */
    public function deleteJob(Enlight_Components_Cron_Job $job)
    {
        $this->adapter->deleteJob($job);

        return $this;
    }

    /**
     * Updates a cron job
     *
     * @param \Enlight_Components_Cron_Job $job
     *
     * @throws Enlight_Exception
     *
     * @return Enlight_Components_Cron_Manager
     */
    public function updateJob(Enlight_Components_Cron_Job $job)
    {
        $this->adapter->updateJob($job);

        return $this;
    }

    /**
     * Returns an array of Enlight_Components_Cron_Job from crontab
     *
     * @return Enlight_Components_Cron_Job[]
     */
    public function getAllJobs()
    {
        return $this->adapter->getAllJobs();
    }

    /**
     * Receives a single Cron job defined by its id from crontab
     *
     * @param int $id
     *
     * @return null|Enlight_Components_Cron_Job
     */
    public function getJobById($id)
    {
        $retVal = $this->adapter->getJobById((int) $id);
        if (empty($retVal)) {
            return null;
        }

        return $retVal;
    }

    /**
     * Receives a single cron job by its name from the crontab
     *
     * @param string $name
     *
     * @return null|Enlight_Components_Cron_Job
     */
    public function getJobByName($name)
    {
        $retVal = $this->adapter->getJobByName((string) $name);
        if (empty($retVal)) {
            return null;
        }

        return $retVal;
    }

    /**
     * Receives a single cron job by its action from the crontab
     *
     * @param string $action
     *
     * @return null|Enlight_Components_Cron_Job
     */
    public function getJobByAction($action)
    {
        $retVal = $this->adapter->getJobByAction((string) $action);
        if (empty($retVal)) {
            return null;
        }

        return $retVal;
    }

    /**
     * Adds an job to the crontab
     *
     * @param Enlight_Components_Cron_Job $job
     *
     * @return Enlight_Components_Cron_Manager
     */
    public function addJob(Enlight_Components_Cron_Job $job)
    {
        $this->adapter->createJob($job);

        return $this;
    }

    /**
     * Returns the next cron job who is due to execute
     *
     * @param bool $force
     *
     * @return null|Enlight_Components_Cron_Job
     */
    public function getNextJob($force = false)
    {
        return $this->adapter->getNextJob($force);
    }

    /**
     * Runs a job by handing it over to
     *
     * @param Enlight_Components_Cron_Job $job
     *
     * @throws Throwable
     *
     * @return Enlight_Event_EventArgs
     */
    public function runJob(Enlight_Components_Cron_Job $job)
    {
        // Fix cron action name
        $action = $job->getAction();
        if (strpos($action, 'Shopware_') !== 0) {
            $action = str_replace(' ', '', ucwords(str_replace('_', ' ', $job->getAction())));
            $job->setAction('Shopware_CronJob_' . $action);
        }

        try {
            $this->adapter->startJob($job);
            /** @var Enlight_Components_Cron_EventArgs $jobArgs */
            $jobArgs = new $this->eventArgsClass([
                'subject' => $this,
                'job' => $job,
            ]);
            $jobArgs->setReturn($job->getData());

            $jobArgs = $this->eventManager->notifyUntil(
                $job->getAction(),
                $jobArgs
            );

            if ($jobArgs !== null) {
                $job->setData($jobArgs->getReturn());
                $this->adapter->updateJob($job);
            }

            $this->endJob($job);
            $this->eventManager->notify('Shopware_CronJob_Finished_' . $job->getAction(), [
                'subject' => $this,
                'job' => $job,
            ]);

            return $jobArgs;
        } catch (\Throwable $e) {
            $job->setData(['error' => $e->getMessage()]);

            if ($job->getDisableOnError()) {
                $this->disableJob($job);
            } else {
                $this->endJob($job);
            }

            $this->eventManager->notify('Shopware_CronJob_Error_' . $action, [
                'subject' => $this,
                'job' => $job,
            ]);

            throw $e;
        }
    }

    /**
     * Ends a job by handing it over to
     *
     * @param Enlight_Components_Cron_Job $job
     */
    protected function endJob(Enlight_Components_Cron_Job $job)
    {
        $now = new Zend_Date();
        $now = $now->getTimestamp();
        $interval = $job->getInterval();
        $next = $job->getNext()->getTimestamp();
        if ($interval > 0) {
            do {
                $next += $interval;
            } while ($now >= $next);
        } else {
            $next = $now;
        }
        $job->setNext($next);
        $job->setEnd($now);

        $this->adapter->updateJob($job);
    }
}
