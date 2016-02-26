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

/**
 * Class PriceStruct
 * @package Shopware\Bundle\PluginInstallerBundle\Struct
 */
class SubscriptionStateStruct implements \JsonSerializable
{
    /**
     * @var int
     */
    private $isShopUpgraded;

    /**
     * @var []
     */
    private $notUpgradedPlugins;

    /**
     * @var []
     */
    private $wrongVersionPlugins;

    /**
     * @var []
     */
    private $expiredPluginSubscriptions;

    /**
     * @param boolean $isShopUpgraded
     * @param [] $notUpgradedPlugins
     * @param [] $wrongVersionPlugins
     * @param [] $expiredPluginSubscriptions
     */
    public function __construct($isShopUpgraded, $notUpgradedPlugins, $wrongVersionPlugins, $expiredPluginSubscriptions)
    {
        $this->isShopUpgraded = $isShopUpgraded;
        $this->notUpgradedPlugins = $notUpgradedPlugins;
        $this->wrongVersionPlugins = $wrongVersionPlugins;
        $this->expiredPluginSubscriptions = $expiredPluginSubscriptions;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return boolean
     */
    public function getIsShopUpgraded()
    {
        return $this->isShopUpgraded;
    }

    /**
     * @return []
     */
    public function getNotUpgradedPlugins()
    {
        return $this->notUpgradedPlugins;
    }

    /**
     * @return []
     */
    public function getWrongVersionPlugins()
    {
        return $this->wrongVersionPlugins;
    }

    /**
     * @return []
     */
    public function getExpiredPluginSubscriptions()
    {
        return $this->expiredPluginSubscriptions;
    }
}
