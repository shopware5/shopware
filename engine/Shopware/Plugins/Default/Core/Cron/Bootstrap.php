<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
 */
class Shopware_Plugins_Core_Cron_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    protected $results = array();

    public function install()
    {
        $this->subscribeEvent(
            'Enlight_Controller_Dispatcher_ControllerPath_Backend_Cron',
            'onGetControllerPath'
        );
        $this->subscribeEvent(
            'Enlight_Controller_Front_AfterSendResponse',
            'onAfterSendResponse'
        );
        $this->subscribeEvent(
            'Enlight_Bootstrap_InitResource_Cron',
            'onInitResourceCron'
        );
        return true;
    }

    public function onGetControllerPath(Enlight_Event_EventArgs $args)
    {
        return $this->Path() . 'Cron.php';
    }

    public function onAfterSendResponse(Enlight_Event_EventArgs $args)
    {
        //Shopware()->Cron()->runCronJobs();
    }

    public function onInitResourceCron(Enlight_Event_EventArgs $args)
    {
        $eventManager = $this->Application()->Events();
        $adapter = new Enlight_Components_Cron_Adapter_DbTable(array(
            'name' => 's_crontab'
        ));
        $manager = new Enlight_Components_Cron_Manager(
            $adapter, $eventManager, 'Shopware_Components_Cron_CronJob'
        );
        return $manager;
    }
}
