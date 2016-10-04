<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
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
        $data['action']           = $job->getAction();
        $data[$this->connection->quoteIdentifier('interval')] = $job->getInterval();
        $data['data']             = serialize($job->getData());
        $data['active']           = ($job->getActive()) ? '1' : '0';
        $data['next']             = ($job->getNext()) ? $job->getNext()->toString('YYYY-MM-dd HH:mm:ss') : null;
        $data['start']            = ($job->getStart()) ? $job->getStart()->toString('YYYY-MM-dd HH:mm:ss') : null;
        $data['end']              = ($job->getEnd()) ? $job->getEnd()->toString('YYYY-MM-dd HH:mm:ss') : null;
        $data['disable_on_error'] = ($job->getDisableOnError()) ? '1' : '0';

        if (is_null($job->getId())) {
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
            ['id'  => $job->getId()]
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
            $row['data'] = unserialize($row['data']);
            $jobs[$row['id']] = new Enlight_Components_Cron_Job($row);
        }

        return $jobs;
    }

    /**
     * {@inheritdoc}
     */
    public function getNextJob($force = false)
    {
        $qb = $this->connection->createQueryBuilder();

        $qb->select('*')
            ->from($this->tableName, 'c')
            ->andWhere('c.active = 1')
            ->andWhere('c.end IS NOT NULL')
            ->orderBy('c.next');

        if (!$force) {
            $qb->andWhere('c.next <= :dateNow');
            $qb->setParameter('dateNow', new DateTime(), 'datetime');
        }

        $row = $qb->execute()->fetch();

        if (!$row) {
            return null;
        }

        $row['data'] = unserialize($row['data']);

        return new Enlight_Components_Cron_Job($row);
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
     * @param $column
     * @param $value
     * @return Enlight_Components_Cron_Job|null
     */
    private function getJobByColumn($column, $value)
    {
        $qb = $this->connection->createQueryBuilder();

        $field = 'c.'.$column;

        $qb->select('*')
            ->from($this->tableName, 'c')
            ->andWhere($field . '= :value')
            ->setParameter('value', $value);

        $row = $qb->execute()->fetch();

        if (!$row) {
            return null;
        }

        $row['data'] = unserialize($row['data']);

        return new Enlight_Components_Cron_Job($row);
    }
}
