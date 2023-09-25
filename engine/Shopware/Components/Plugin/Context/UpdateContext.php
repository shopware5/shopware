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

namespace Shopware\Components\Plugin\Context;

use Shopware\Models\Plugin\Plugin;

class UpdateContext extends InstallContext
{
    /**
     * @var string
     */
    private $updateVersion;

    /**
     * @param string $shopwareVersion
     * @param string $currentVersion
     * @param string $updateVersion
     */
    public function __construct(
        Plugin $plugin,
        $shopwareVersion,
        $currentVersion,
        $updateVersion
    ) {
        parent::__construct($plugin, $shopwareVersion, $currentVersion);
        $this->updateVersion = $updateVersion;
    }

    /**
     * @return string
     */
    public function getUpdateVersion()
    {
        return $this->updateVersion;
    }
}
