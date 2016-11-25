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

use Shopware\Bundle\PluginInstallerBundle\Context\ListingRequest;
use Shopware\Bundle\PluginInstallerBundle\Context\PluginsByTechnicalNameRequest;
use Shopware\Bundle\PluginInstallerBundle\Struct\ListingResultStruct;
use Shopware\Bundle\PluginInstallerBundle\Struct\PluginStruct;

/**
 * Class PluginLocalService
 *
 * @package Shopware\Bundle\PluginInstallerBundle\Service
 */
interface PluginLocalServiceInterface
{
    /**
     * @param ListingRequest $context
     *
     * @return ListingResultStruct
     */
    public function getListing(ListingRequest $context);

    /**
     * @param PluginsByTechnicalNameRequest $context
     *
     * @return PluginStruct
     */
    public function getPlugin(PluginsByTechnicalNameRequest $context);

    /**
     * @param PluginsByTechnicalNameRequest $context
     *
     * @return PluginStruct[]
     */
    public function getPlugins(PluginsByTechnicalNameRequest $context);

    /**
     * @return array indexed by technical name, value contains the version
     */
    public function getPluginsForUpdateCheck();
}
