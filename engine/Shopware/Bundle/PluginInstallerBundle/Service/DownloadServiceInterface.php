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
namespace Shopware\Bundle\PluginInstallerBundle\Service;

use Shopware\Bundle\PluginInstallerBundle\Context\DownloadRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\MetaRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\RangeDownloadRequest;
use Shopware\Bundle\PluginInstallerBundle\Struct\MetaStruct;
use ShopwarePlugins\SwagUpdate\Components\Steps\FinishResult;
use ShopwarePlugins\SwagUpdate\Components\Steps\ValidResult;

/**
 * @package Shopware\Bundle\PluginInstallerBundle\Service
 */
interface DownloadServiceInterface
{
    /**
     * @param RangeDownloadRequest $request
     *
     * @return FinishResult|ValidResult
     */
    public function downloadRange(RangeDownloadRequest $request);

    /**
     * @param string $file
     * @param string $pluginName
     *
     * @throws \Exception
     */
    public function extractPluginZip($file, $pluginName);

    /**
     * @param MetaRequest $request
     *
     * @return MetaStruct
     */
    public function getMetaInformation(MetaRequest $request);

    /**
     * @param DownloadRequest $request
     *
     * @return bool
     */
    public function download(DownloadRequest $request);
}
