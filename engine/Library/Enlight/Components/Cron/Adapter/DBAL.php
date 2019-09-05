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

use Doctrine\DBAL\Connection;

/**
 * Database adapter for the cron job component.
 *
 * This adapter allows you to implement cron tasks in your application.
 * The adapter handles all action between the controller and database.
 */
class Enlight_Components_Cron_Adapter_DBAL implements Enlight_Components_Cron_Adapter
{
    /**
     * Database table name. Here are the Jobs are stored
     *
     * @var string
     */
    protected $tableName = 's_crontab';

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Enlight_Components_Cron_Job[]|null
     */
    private $allJobsList;

    /**
     * @var Enlight_Components_Cron_Job[]|null
     */
    private $overdueJobsList;

    /**
     * @param Connection $connection
     */
    public function __construct(Doctrine\DBAL\Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * {@inheritdoc}
     */
    public function updateJob(Enlight_Components_Cron_Job $job)
    {
        $data = [];
        $data['action'] = $job->getAction();
        $data[$this->connection->quoteIdentifier('interval')] = $job->getInterval();
        $data['data'] = serialize($job->getData());
        $data['active'] = $job->getActive() ? '1' : '0';
        $data['next'] = $job->getNext() ? $job->getNext()->toString('YYYY-MM-dd HH:mm:ss') : null;
        $data['start'] = $job->getStart() ? $job->getStart()->toString('YYYY-MM-dd HH:mm:ss') : null;
        $data['end'] = $job->getEnd() ? $job->getEnd()->toString('YYYY-MM-dd HH:mm:ss') : null;
        $data['disable_on_error'] = $job->getDisableOnError() ? '1' : '0';
        $data['name'] = $job->getName();

        if ($job->getId() === null) {
            $this->connection->insert($this->tableName, $data);
        } else {
            $this->connection->update(
                $this->tableName,
                $data,
                ['id' => $job->getId()]
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    public function startJob(Enlight_Components_Cron_Job $job)
    {
        $job->setStart();
        $this->updateJob($job);

        return $this->connection->update(
            $this->tableName,
            ['end' => null],
            ['id' => $job->getId()]
        ) > 0;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllJobs()
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from($this->tableName, 'c');

        $ignoreActive = true;
        if (!$ignoreActive) {
            $qb->andWhere('c.active = true');
        }

        $rows = $qb->execute()->fetchAll();

        $jobs = [];
        foreach ($rows as $row) {
            $row['data'] = unserialize($row['data'], ['allowed_classes' => false]);
            $jobs[$row['id']] = new Enlight_Components_Cron_Job($row);
        }

        return $jobs;
    }

    /**
     * {@inheritdoc}
     */
    public function getNextJob($force = false)
    {
        if ($force) {
            if (!$this->allJobsList) {
                $this->allJobsList = $this->getAllJobs();
            }

            return array_pop($this->allJobsList);
        }

        if (!$this->overdueJobsList) {
            $this->overdueJobsList = $this->getOverdueJobs();
        }

        while (($nextJob = array_pop($this->overdueJobsList)) !== null) {
            if ($this->isJobStillOverdue($nextJob->getId())) {
                return $nextJob;
            }
        }

        return null;
    }

    /**
     * {@inheritdoc}
     */
    public function getJobById($id)
    {
        return $this->getJobByColumn('id', $id);
    }

    /**
     * {@inheritdoc}
     */
    public function getJobByName($name)
    {
        return $this->getJobByColumn('name', $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getJobByAction($action)
    {
        return $this->getJobByColumn('action', $action);
    }

    /**
     * {@inheritdoc}
     */
    public function createJob(Enlight_Components_Cron_Job $job)
    {
        $this->updateJob($job);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteJob(Enlight_Components_Cron_Job $job)
    {
        $this->connection->delete(
            $this->tableName,
            ['id' => $job->getId()]
        );
    }

    /**
     * Internal helper method to grep data based on a given column name.
     *
     * @param string $column
     * @param string $value
     *
     * @return Enlight_Components_Cron_Job|null
     */
    private function getJobByColumn($column, $value)
    {
        $qb = $this->connection->createQueryBuilder();

        $field = 'c.' . $column;

        $qb->select('*')
            ->from($this->tableName, 'c')
            ->andWhere($field . '= :value')
            ->setParameter('value', $value);

        $row = $qb->execute()->fetch();

        if (!$row) {
            return null;
        }

        $row['data'] = unserialize($row['data'], ['allowed_classes' => false]);

        return new Enlight_Components_Cron_Job($row);
    }

    /**
     * @return Enlight_Components_Cron_Job[]
     */
    private function getOverdueJobs()
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from($this->tableName, 'c')
            ->andWhere('c.active = 1')
            ->andWhere('c.end IS NOT NULL')
            ->orderBy('c.next')
            ->andWhere('c.next <= :dateNow')
            ->setParameter('dateNow', new DateTime(), 'datetime');

        $rows = $qb->execute()->fetchAll();

        $overdueJobsList = [];
        foreach ($rows as $row) {
            $row['data'] = unserialize($row['data'], ['allowed_classes' => false]);
            $overdueJobsList[$row['id']] = new Enlight_Components_Cron_Job($row);
        }

        return $overdueJobsList;
    }

    /**
     * @return bool
     */
    private function isJobStillOverdue($jobId)
    {
        $qb = $this->connection->createQueryBuilder();
        $qb->select('*')
            ->from($this->tableName, 'c')
            ->andWhere('c.active = 1')
            ->andWhere('c.end IS NOT NULL')
            ->andWhere('c.id = :jobId')
            ->andWhere('c.next <= :dateNow')
            ->setParameter('jobId', $jobId)
            ->setParameter('dateNow', new DateTime(), 'datetime');
        $row = $qb->execute()->fetch();
        if (!$row) {
            return false;
        }

        return true;
    }
}
