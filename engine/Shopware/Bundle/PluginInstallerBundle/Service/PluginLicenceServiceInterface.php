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

use Shopware\Bundle\PluginInstallerBundle\Context\UpdateLicencesRequest;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginInformationStruct;

interface PluginLicenceServiceInterface
{
    /**
     * @param string $licenceKey
     *
     * @return int
     */
    public function importLicence($licenceKey);

    /**
     * @param UpdateLicencesRequest $request
     *
     * @return array
     */
    public function updateLicences(UpdateLicencesRequest $request);

    /**
     * function to get expired and soon expiring plugins
     *
     * @return PluginInformationStruct[]
     */
    public function getExpiringLicenses();

    /**
     * function to get only expired plugins
     *
     * @return PluginInformationStruct[]
     */
    public function getExpiredLicenses();

    /**
     * @param PluginInformationStruct[] $pluginInformation
     * @param string                    $domain
     */
    public function updateLocalLicenseInformation(array $pluginInformation, $domain);
}
