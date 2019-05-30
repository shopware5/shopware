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

namespace Shopware\Components\Plugin\Context;

use Shopware\Models\Plugin\Plugin;

class UninstallContext extends InstallContext
{
    /**
     * @var bool
     */
    private $keepUserData;

    /**
     * @param string $shopwareVersion
     * @param string $currentVersion
     * @param bool   $keepUserData
     */
    public function __construct(
        Plugin $plugin,
        $shopwareVersion,
        $currentVersion,
        $keepUserData
    ) {
        parent::__construct($plugin, $shopwareVersion, $currentVersion);
        $this->keepUserData = $keepUserData;
    }

    /**
     * @return bool
     */
    public function keepUserData()
    {
        return $this->keepUserData;
    }
}
