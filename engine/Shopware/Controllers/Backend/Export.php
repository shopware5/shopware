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

use Shopware\Components\CSRFWhitelistAware;
use Symfony\Component\HttpFoundation\Response;

/**
 * This controller is used by the ProductFeed module.
 * The ProductFeed module will call this controller to export the chosen ProductFeed with all options.
 * The controller uses the base class sExport for all export relevant methods.
 * Sets a different header to return a downloadable export file.
 */
class Shopware_Controllers_Backend_Export extends Enlight_Controller_Action implements CSRFWhitelistAware
{
    /**
     * @var sExport
     */
    private $export;

    /**
     * Init controller method
     *
     * Disables the authorization-checking and template renderer.
     */
    public function init()
    {
        Shopware()->Plugins()->Backend()->Auth()->setNoAuth();
        Shopware()->Plugins()->Controller()->ViewRenderer()->setNoRender();
        $this->Front()->setParam('disableOutputBuffering', true);
        $this->export = Shopware()->Modules()->Export();
    }

    /**
     * Index action method
     *
     * Creates the export product.
     */
    public function indexAction()
    {
        $this->prepareExport();
        $this->sendHeaders();

        $productFeed = Shopware()->Models()->getRepository('\Shopware\Models\ProductFeed\ProductFeed')->find((int) $this->Request()->feedID);

        // Live generation
        if ($productFeed->getInterval() === 0) {
            $this->generateExport('php://output');

            return;
        }

        $directory = $this->createOutputDirectory();
        $fileName = $productFeed->getHash() . '_' . $productFeed->getFileName();
        $filePath = $directory . $fileName;

        if ($productFeed->getInterval() === -1 && file_exists($filePath)) {
            readfile($filePath);

            return;
        }

        $diffInterval = time();
        if ($productFeed->getCacheRefreshed()) {
            $diffInterval = $diffInterval - $productFeed->getCacheRefreshed()->getTimestamp();
        }

        if ($diffInterval >= $productFeed->getInterval() || !file_exists($filePath)) {
            $this->generateExport($filePath);

            // update last refresh
            $productFeed->setCacheRefreshed('now');
            Shopware()->Models()->persist($productFeed);
            Shopware()->Models()->flush($productFeed);
        }

        if (!file_exists($filePath)) {
            $this->Response()
                ->clearHeaders()
                ->setStatusCode(Response::HTTP_NO_CONTENT)
                ->appendBody('Empty feed found.');

            return;
        }

        readfile($filePath);
    }

    /**
     * Returns a list with actions which should not be validated for CSRF protection
     *
     * @return string[]
     */
    public function getWhitelistedCSRFActions()
    {
        return [
            'index',
        ];
    }

    /**
     * @param string $output Path to output file
     */
    private function generateExport($output)
    {
        $outputHandle = fopen($output, 'w');

        $this->export->sSmarty = $this->View()->Engine();
        $this->export->sInitSmarty();

        // Export the feed
        $this->export->executeExport($outputHandle);
    }

    /**
     * initialize the base class sExport
     */
    private function prepareExport()
    {
        $this->export->sSYSTEM = Shopware()->System();
        $this->export->sFeedID = (int) $this->Request()->feedID;
        $this->export->sHash = $this->Request()->hash;

        $this->export->sInitSettings();
    }

    /**
     * set feed specific options to the export and sets
     * the right header
     */
    private function sendHeaders()
    {
        $encoding = $this->getExportEncoding();
        $contentType = $this->getExportContentType();

        $this->Response()->headers->set('content-type', $contentType . ';charset=' . $encoding);
        $this->Response()->sendHeaders();
    }

    /**
     * @return string
     */
    private function getExportEncoding()
    {
        if (!empty($this->export->sSettings['encodingID']) && $this->export->sSettings['encodingID'] == 2) {
            return 'utf-8';
        }

        return 'iso-8859-1';
    }

    /**
     * @return string
     */
    private function getExportContentType()
    {
        if (!empty($this->export->sSettings['formatID']) && $this->export->sSettings['formatID'] == 3) {
            return 'text/xml';
        }

        return 'text/x-comma-separated-values';
    }

    /**
     * @return string Path to new output directory
     */
    private function createOutputDirectory()
    {
        $dirName = $this->container->getParameter('kernel.cache_dir');
        $dirName .= '/productexport/';
        if (!file_exists($dirName)) {
            mkdir($dirName);
        }

        return $dirName;
    }
}
