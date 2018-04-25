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
class Shopware_Plugins_Core_Privacy_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @return string
     */
    public function getLabel()
    {
        return 'Privacy Plugin';
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return '1.0.0';
    }

    /**
     * @return array
     */
    public function getInfo()
    {
        return [
            'version' => $this->getVersion(),
            'label' => $this->getLabel(),
        ];
    }

    public function install()
    {
        $this->subscribeEvent(
            'Shopware_CronJob_cleanUpUsersAndOrders',
            'cleanupUsersAndOrders'
        );

        $this->createCronJob(
            'Cron Privacy',
            'cleanUpUsersAndOrders',
            86400,
            true
        );

        $form = $this->Form();
        $parent = $this->Forms()->findOneBy(['name' => 'Core']);
        $form->setName('CronPrivacy');
        $form->setLabel('Cron-Privacy');

        $months = range(1, 12);

        $form->setElement('combo', 'monthInterval', [
            'label' => 'Month interval ',
            'editable' => true,
            'required' => true,
            'value' => 6,
            'store' => $months,
        ]);

        $form->setElement('number', 'orders', [
            'label' => 'Minimum order amount',
            'editable' => true,
            'required' => true,
            'value' => 0,
        ]);

        $form->setParent($parent);

        return true;
    }

    public function CleanUpUsersAndOrders(Shopware_Components_Cron_CronJob $job)
    {
        /** @var Shopware\Bundle\AccountBundle\Service\MaintenanceService $accountMaintenance */
        $accountMaintenance = $this->get('shopware_account.maintenance_service');
        $accountMaintenance->cleanupGuestUsers(
            $this->Config()->get('monthInterval'),
            $this->Config()->get('orders')
        );

        $accountMaintenance->cleanupCanceledOrders($this->Config()->get('monthInterval'));
    }
}
