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
 * @package    Enlight_Cron
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 * @version    $Id$
 * @author     $Author$
 */

/**
 * Database adapter for the cron job component.
 *
 * This adapter allows you to implement cron tasks in your application.
 * The adapter handles all action between the controller and database.
 *
 * @category   Enlight
 * @package    Enlight_Cron
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */

class Enlight_Components_Cron_Adapter_DbTable extends Zend_Db_Table_Abstract implements Enlight_Components_Cron_Adapter
{
    /**
     * Database table name. Here are the Jobs are stored
     *
     * @var string
     */
    protected $_name = 'crontab';

    /**
     * Primary Key used for delete and update actions
     *
     * @var string
     */
    protected $_primary = 'id';

    /**
     * Name mapping between what the app and the real database.
     * Knows following Columns<br>
     * -id - An integer field; unique; Primary Kex<br>
     * -name - String(255); Name or descriptions of the Job eg. Check Stock<br>
     * -action - Unique string(255) field. action becomes the event name during the execution of the job<br>
     * -next - datetime; stores the date when the next run is due<br>
     * -start - datetime; stores the last date when the job has been called<br>
     * -end - datetime; stores the date/time an which the job stopped<br>
     * -interval - Integer field; Stores the delta time between two runs in seconds.<br>
     * -active - boolean field; 1 = active 0 = inactive.
     *
     * @var array
     */
    protected $_columns = array(
        'id' => 'id',
        'name' => 'name',
        'action' => 'action',
        'data' => 'data',
        'next' => 'next',
        'start' => 'start',
        'interval' => 'interval',
        'active' => 'active',
        'end' => 'end'
    );

    /**
     * Constructor - This class is derived from Zend_Db_Table.
     * An array of options can be provided to setup the object. The array should include a mapping
     * of database columns.<br>
     * -idColumn - An integer field; unique; Primary Kex<br>
     * -nameColumn - String(255); Name or descriptions of the Job eg. Check Stock<br>
     * -actionColumn - Unique string(255) field. action becomes the event name during the execution of the job<br>
     * -nextColumn - datetime; stores the date when the next run is due<br>
     * -startColumn - datetime; stores the last date when the job has been called<br>
     * -endColumn - datetime; stores the date/time an which the job stopped<br>
     * -intervalColumn - Integer field; Stores the delta time between two runs in seconds.<br>
     * -activeColumn - boolean field; 1 = active 0 = inactive.<br>
     *
     * @param array|null $options
     * @see http://framework.zend.com/manual/en/zend.db.table.html
     */
    public function __construct(array $options = array())
    {
        parent::__construct($options);
    }

    /**
     * The options for this class will be set through this method
     * Every key of the array which ends with Column will be used as mapping indicator
     * every other option will be given over to the parent class.
     *
     * @param array $options
     * @return Zend_Db_Table_Abstract
     */
    public function setOptions(array $options)
    {
        foreach ($options as $key => $option) {
            if (substr($key, -6) == 'Column') {
                $this->_columns[substr($key, 0, -6)] = (string) $option;
            }
        }
        return parent::setOptions($options);
    }

    /**
     * Adds a job to the crontab.
     *
     * @param Enlight_Components_Cron_Job $job
     * @throws Enlight_Exception
     * @return Enlight_Components_Cron_Adapter
     */
    public function createJob(Enlight_Components_Cron_Job $job)
    {
        return $this->updateJob($job);
    }

    /**
     * Updates a cron job in the cron tab
     *
     * @param Enlight_Components_Cron_Job $job
     * @return Enlight_Components_Cron_Adapter
     * @throws Enlight_Exception
     */
    public function updateJob(Enlight_Components_Cron_Job $job)
    {
        $data = array();
        foreach ($this->_columns as $key => $mapping) {
            if ($key == $this->_primary) {
                continue;
            }
            $value = $job->$key;
            if ($key === 'data') {
                $value = serialize($job->$key);
            }
            $data[$mapping] = $value;
        }
        if (is_null($job->getId())) {
            $this->insert($data);
        } else {
            $this->update($data, array(
                $this->getAdapter()->quoteIdentifier($this->_primary) . ' = ?' => $job->getId()
            ));
        }
    }

    /**
     * @param Enlight_Components_Cron_Job $job
     * @return boolean
     */
    public function startJob(Enlight_Components_Cron_Job $job)
    {
        $job->setStart();
        $this->updateJob($job);
        return $this->update(array(
            $this->_columns['end'] => null
        ), array(
            $this->getAdapter()->quoteIdentifier($this->_primary) . ' = ?' => $job->getId()
        )) > 0;
    }

    /**
     * Returns an array of Enlight_Components_Cron_Job from the crontab
     * If no cron jobs found the method will return an empty array
     *
     * @param bool $ignoreActive if set true the active flag will be ignored
     * @return array
     */
    public function getAllJobs($ignoreActive = false)
    {
        $where = null;
        if (!$ignoreActive) {
            $where = array(
                $this->getAdapter()->quoteIdentifier($this->_columns['active']) . ' = ?' => 1
            );
        }
        $rows = $this->fetchAll($where);
        if (count($rows) === 0) {
            return array();
        }
        $retVal = array();
        foreach ($rows as $row) {
            $row['data'] = unserialize($row['data']);
            $retVal[$row['id']] = new Enlight_Components_Cron_Job($row->toArray());
        }
        return $retVal;
    }

    /**
     * Returns the next cron job based on the next date field
     *
     * @return null|Enlight_Components_Cron_Job
     */
    public function getNextJob()
    {
        $sql = $this->select();
        $sql->where($this->getAdapter()->quoteIdentifier($this->_columns['active']) . ' = 1')
            ->where($this->getAdapter()->quoteIdentifier($this->_columns['end']). ' IS NOT NULL')
            ->where($this->getAdapter()->quoteIdentifier($this->_columns['next']) . ' <=?', new Zend_Date());

        $row = $this->fetchRow($sql);
        if (count($row) === 0) {
            return null;
        }
        $row['data'] = unserialize($row['data']);
        $retVal = new Enlight_Components_Cron_Job($row->toArray());

        return $retVal;
    }

    /**
     * Internal helper method to grep data based on a given column name.
     *
     * @param $column
     * @param $value
     * @return Enlight_Components_Cron_Job|null
     */
    protected function getJobByColumn($column, $value)
    {
        $row = $this->fetchRow(array(
            $this->getAdapter()->quoteIdentifier($column) . ' = ?' => $value
        ));
        if (count($row) === 0) {
            return null;
        }
        $row['data'] = unserialize($row['data']);
        return new Enlight_Components_Cron_Job($row->toArray());
    }

    /**
     * Receives a single Cron job defined by its id from the crontab
     *
     * @param Int $id
     * @return Enlight_Components_Cron_Job|null
     */
    public function getJobById($id)
    {
        return $this->getJobByColumn($this->_columns['id'], $id);
    }

    /**
     * Returns a single cron job by its name
     *
     * @param String $name
     * @return Enlight_Components_Cron_Job
     */
    public function getJobByName($name)
    {
        return $this->getJobByColumn($this->_columns['name'], $name);
    }


    /**
     * Removes an job from the cron tab
     *
     * @param Enlight_Components_Cron_Job $job
     * @return Enlight_Components_Cron_Adapter_DbTable
     */
    public function deleteJob(Enlight_Components_Cron_Job $job)
    {
        $this->delete(array(
            $this->getAdapter()->quoteIdentifier($this->_primary) . ' = ?' => $job->getId()
        ));
        return $this;
    }
}
