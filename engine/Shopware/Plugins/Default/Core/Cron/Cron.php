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
 */
class Shopware_Controllers_Backend_Cron extends Enlight_Controller_Action
{
    public function init()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
    }

    public function indexAction()
    {
        if (!Shopware()->Plugins()->Core()->Cron()->authorizeCronAction($this->Request())) {
            $this->Response()
                ->clearHeaders()
                ->setHttpResponseCode(403)
                ->appendBody("Forbidden");
            return;
        }

        /** @var $cronManager Enlight_Components_Cron_Manager */
        $cronManager = Shopware()->Cron();

        set_time_limit(0);
        while (($job = $cronManager->getNextJob()) !== null) {
            echo "Processing " . $job->getName() . "\n";
            $cronManager->runJob($job);
        }
    }
}
