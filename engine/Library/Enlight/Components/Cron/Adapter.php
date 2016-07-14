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

/**
 * Interface for specific cron job adapter.
 *
 * The Enlight_Components_Cron_Adapter interface provides an easy way to implement own cron job adapter.
 *
 * @category   Enlight
 * @package    Enlight_Cron
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
interface Enlight_Components_Cron_Adapter
{
    /**
     * Updates a cron job in the cron tab
     *
     * @param Enlight_Components_Cron_Job $job
     * @return Enlight_Components_Cron_Manager
     */
    public function updateJob(Enlight_Components_Cron_Job $job);

    /**
     * @param Enlight_Components_Cron_Job $job
     * @return mixed
     */
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
     * @return null|Enlight_Components_Cron_Job
     */
    public function getNextJob($force = false);

    /**
     * Receives a single Cron job defined by its id from the crontab
     *
     * @abstract
     * @param Int $id
     * @return Enlight_Components_Cron_Job
     */
    public function getJobById($id);

    /**
     * Receives a single cron job by its name
     *
     * @abstract
     * @param String $name
     * @return Enlight_Components_Cron_Job
     */
    public function getJobByName($name);

    /**
     * Receives a single cron job by its action name
     *
     * @abstract
     * @param String $action
     * @return Enlight_Components_Cron_Job
     */
    public function getJobByAction($action);

    /**
     * Adds a job to the crontab
     *
     * @abstract
     * @param Enlight_Components_Cron_Job $job
     * @return void
     */
    public function createJob(Enlight_Components_Cron_Job $job);

    /**
     * Removes an job from the crontab
     *
     * @abstract
     * @param Enlight_Components_Cron_Job $job
     * @return void
     */
    public function deleteJob(Enlight_Components_Cron_Job $job);
}
