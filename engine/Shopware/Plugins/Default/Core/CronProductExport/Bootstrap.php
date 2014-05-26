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
 * Shopware Cron to generate the product export files
 */
class Shopware_Plugins_Core_CronProductExport_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * @var \Shopware\Models\ProductFeed\Repository
     */
    protected $productFeedRepository = null;

    /**
     * Bootstrap Installation method
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Shopware_CronJob_ProductExport',
            'onRun'
        );
        $this->createCronJob("Product Export", "ProductExport", 86400);

        return true;
    }

    /**
     * @param Enlight_Components_Cron_EventArgs $job
     */
    public function onRun(Enlight_Components_Cron_EventArgs $job)
    {
        $this->exportProductFiles();
    }

    /**
     * starts all product export for all active product feeds
     *
     * @return string
     */
    public function exportProductFiles()
    {
        $productFeedRepository = Shopware()->Models()->getRepository(
            'Shopware\Models\ProductFeed\ProductFeed'
        );
        $activeFeeds = $productFeedRepository->getActiveListQuery()->getResult();

        $export = Shopware()->Modules()->Export();
        $export->sSYSTEM = Shopware()->System();
        $export->sDB = Shopware()->AdoDb();
        $sSmarty = Shopware()->Template();

        foreach ($activeFeeds as $feedModel) {
            /** @var $feedModel Shopware\Models\ProductFeed\ProductFeed */
            if ($feedModel->getInterval() == 0) {
                continue;
            }
            $export->sFeedID = $feedModel->getId();
            $export->sHash = $feedModel->getHash();
            $export->sInitSettings();
            $export->sSmarty = clone $sSmarty;
            $export->sInitSmarty();

            $fileName = $feedModel->getHash() . '_' . $feedModel->getFileName();
            $handleResource = fopen(Shopware()->DocPath() . 'cache/productexport/' . $fileName, 'w');
            $export->executeExport($handleResource);
        }

        return true;
    }
}
