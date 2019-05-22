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
 * The Enlight_Components_Cron_EventArgs will be passed to cron job listener method.
 *
 * Extends the enlight event arguments with cron job specified properties.
 * The arguments will be passed by the cron job manager to the running cron job.
 *
 * @category   Enlight
 * @package    Enlight_Cron
 * @copyright  Copyright (c) 2011, shopware AG (http://www.shopware.de)
 * @license    http://enlight.de/license     New BSD License
 */
class Enlight_Components_Cron_EventArgs extends Enlight_Event_EventArgs
{
    /**
     * Returns the Enlight_Components_Cron_Job
     *
     * @return Enlight_Components_Cron_Job
     */
    public function getJob()
    {
        return $this->get('job');
    }
}
