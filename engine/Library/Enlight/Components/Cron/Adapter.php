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
 * Interface for specific cron job adapter.
 *
 * The Enlight_Components_Cron_Adapter interface provides an easy way to implement own cron job adapter.
 *
 * @category   Enlight
 * @package    Enlight_Cron
 *
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
interface Enlight_Components_Cron_Adapter
{
    /**
     * Updates a cron job in the cron tab
     *
     * @return Enlight_Components_Cron_Manager
     */
    public function updateJob(Enlight_Components_Cron_Job $job);

    public function startJob(Enlight_Components_Cron_Job $job);

    /**
     * Returns an array of Enlight_Components_Cron_Job from the crontab
     *
     * @return Enlight_Components_Cron_Job[]
     */
    public function getAllJobs();

    /**
     * Returns the next cron job
     *
     * @param bool $force
     *
     * @return Enlight_Components_Cron_Job|null
     */
    public function getNextJob($force = false);

    /**
     * Receives a single Cron job defined by its id from the crontab
     *
     * @abstract
     *
     * @param int $id
     *
     * @return Enlight_Components_Cron_Job|null
     */
    public function getJobById($id);

    /**
     * Receives a single cron job by its name
     *
     * @abstract
     *
     * @param string $name
     *
     * @return Enlight_Components_Cron_Job|null
     */
    public function getJobByName($name);

    /**
     * Receives a single cron job by its action name
     *
     * @abstract
     *
     * @param string $action
     *
     * @return Enlight_Components_Cron_Job|null
     */
    public function getJobByAction($action);

    /**
     * Adds a job to the crontab
     *
     * @abstract
     *
     * @return void
     */
    public function createJob(Enlight_Components_Cron_Job $job);

    /**
     * Removes an job from the crontab
     *
     * @abstract
     *
     * @return void
     */
    public function deleteJob(Enlight_Components_Cron_Job $job);
}
