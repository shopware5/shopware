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

use Shopware\Models\Banner\Banner;

/**
 * Shopware Backend Tacking
 */
class Shopware_Controllers_Frontend_Tracking extends Enlight_Controller_Action
{
    /**
     * Needed for unit tests
     *
     * @var
     * @scope private
     */
    public static $testRepository;

    /**
     * Disable template engine for all actions and enable JSON Render - spare index and load action
     *
     * @codeCoverageIgnore
     */
    public function preDispatch()
    {
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
    }

    /**
     * Tracks how many clicks on a single banner are clicked.
     * If we have a valid link this action will redirect the browser accordingly
     *
     * @return bool
     */
    public function countBannerClickAction()
    {
        $bannerId = $this->Request()->getParam('bannerId', null);
        if (is_null($bannerId)) {
            return false;
        }
        /** @var $bannerMgn \Shopware\Models\Banner\Repository */
        $bannerMgn = Shopware()->Models()->getRepository(Banner::class);
        $banner = $bannerMgn->findOneBy(['id' => $bannerId]);
        if (is_null($banner)) {
            return false;
        }
        /** @var $statRepository \Shopware\Models\Tracking\Repository */
        $statRepository = Shopware()->Models()->getRepository('\Shopware\Models\Tracking\Banner');

        $bannerStatistics = $statRepository->getOrCreateBannerStatsModel($bannerId);
        $bannerStatistics->increaseClicks();
        Shopware()->Models()->flush($bannerStatistics);
        // speichern
        $jumpTarget = $banner->getLink();
        if (!empty($jumpTarget)) {
            $this->redirect($jumpTarget);
        }

        return true;
    }

    /**
     * Collects the numbers of view
     *
     * @return bool
     */
    public function countBannerViewAction()
    {
        $bannerId = $this->Request()->getParam('bannerId', null);
        if (is_null($bannerId)) {
            return false;
        }
        try {
            /** @var $statRepository \Shopware\Models\Tracking\Repository */
            $statRepository = Shopware()->Models()->getRepository('\Shopware\Models\Tracking\Banner');
            $bannerStatistics = $statRepository->getOrCreateBannerStatsModel($bannerId);
            $bannerStatistics->increaseViews();
            Shopware()->Models()->flush($bannerStatistics);
        } catch (Exception $e) {
            return false;
        }

        return true;
    }
}
