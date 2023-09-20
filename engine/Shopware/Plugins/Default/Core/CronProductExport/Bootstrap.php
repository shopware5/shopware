<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

use Shopware\Models\ProductFeed\ProductFeed;

/**
 * Shopware Cron to generate the product export files
 */
class Shopware_Plugins_Core_CronProductExport_Bootstrap extends Shopware_Components_Plugin_Bootstrap
{
    /**
     * Bootstrap Installation method
     *
     * @return bool
     */
    public function install()
    {
        $this->subscribeEvent(
            'Shopware_CronJob_ProductExport',
            'onRun'
        );
        $this->createCronJob('Product Export', 'ProductExport');

        return true;
    }

    public function onRun(Enlight_Components_Cron_EventArgs $job)
    {
        $this->exportProductFiles();

        return sprintf('%s succeeded.', $job->getName());
    }

    /**
     * starts all product export for all active product feeds
     *
     * @throws RuntimeException
     *
     * @return string
     */
    public function exportProductFiles()
    {
        /** @var string $cacheDir */
        $cacheDir = Shopware()->Container()->getParameter('shopware.product_export.cache_dir');
        if (!is_dir($cacheDir)) {
            if (@mkdir($cacheDir, 0777, true) === false) {
                throw new \RuntimeException(sprintf("Unable to create the %s directory (%s)\n", 'Productexport', $cacheDir));
            }
        } elseif (!is_writable($cacheDir)) {
            throw new \RuntimeException(sprintf("Unable to write in the %s directory (%s)\n", 'Productexport', $cacheDir));
        }

        $export = Shopware()->Modules()->Export();
        $export->sSYSTEM = Shopware()->System();
        $sSmarty = Shopware()->Template();

        $productFeedRepository = Shopware()->Models()->getRepository(ProductFeed::class);
        $activeFeeds = $productFeedRepository->getActiveListQuery()->getResult();
        foreach ($activeFeeds as $feedModel) {
            $fileName = $feedModel->getHash() . '_' . $feedModel->getFileName();
            $filePath = $cacheDir . $fileName;

            if ($feedModel->getInterval() === 0) {
                continue;
            } elseif ($feedModel->getInterval() > 0) {
                $diffInterval = time();
                if ($feedModel->getCacheRefreshed()) {
                    $diffInterval -= $feedModel->getCacheRefreshed()->getTimestamp();
                }

                if ($diffInterval < $feedModel->getInterval() && file_exists($filePath)) {
                    continue;
                }
            }

            $export->sFeedID = $feedModel->getId();
            $export->sHash = $feedModel->getHash();
            $export->sInitSettings();
            $export->sSmarty = clone $sSmarty;
            $export->sInitSmarty();

            $handleResource = fopen($filePath, 'w');
            $export->executeExport($handleResource);
        }

        return true;
    }
}
