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

namespace Shopware\Bundle\PluginInstallerBundle\Struct;

use JsonSerializable;
use Shopware\Components\ObjectJsonSerializeTraitDeprecated;

/**
 * @deprecated in 5.6, will be removed in 5.8 without replacement
 */
class SubscriptionStateStruct implements JsonSerializable
{
    use ObjectJsonSerializeTraitDeprecated;

    /**
     * @var bool
     */
    private $isShopUpgraded;

    /**
     * @var array
     */
    private $notUpgradedPlugins;

    /**
     * @var array
     */
    private $wrongVersionPlugins;

    /**
     * @var array
     */
    private $expiredPluginSubscriptions;

    /**
     * @param bool  $isShopUpgraded
     * @param array $notUpgradedPlugins
     * @param array $wrongVersionPlugins
     * @param array $expiredPluginSubscriptions
     */
    public function __construct($isShopUpgraded, $notUpgradedPlugins, $wrongVersionPlugins, $expiredPluginSubscriptions)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.8. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $this->isShopUpgraded = $isShopUpgraded;
        $this->notUpgradedPlugins = $notUpgradedPlugins;
        $this->wrongVersionPlugins = $wrongVersionPlugins;
        $this->expiredPluginSubscriptions = $expiredPluginSubscriptions;
    }

    /**
     * @return bool
     */
    public function getIsShopUpgraded()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.8. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->isShopUpgraded;
    }

    /**
     * @return array
     */
    public function getNotUpgradedPlugins()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.8. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->notUpgradedPlugins;
    }

    /**
     * @return array
     */
    public function getWrongVersionPlugins()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.8. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->wrongVersionPlugins;
    }

    /**
     * @return array
     */
    public function getExpiredPluginSubscriptions()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.8. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->expiredPluginSubscriptions;
    }
}
