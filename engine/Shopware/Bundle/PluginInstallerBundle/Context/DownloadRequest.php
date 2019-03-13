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

namespace Shopware\Bundle\PluginInstallerBundle\Context;

use Shopware\Bundle\PluginInstallerBundle\Struct\AccessTokenStruct;

class DownloadRequest
{
    /**
     * @var string
     */
    private $technicalName;

    /**
     * @var string
     */
    private $shopwareVersion;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var AccessTokenStruct|null
     */
    private $token;

    /**
     * @param string $technicalName
     * @param string $shopwareVersion
     * @param string $domain
     */
    public function __construct($technicalName, $shopwareVersion, $domain, AccessTokenStruct $token = null)
    {
        $this->technicalName = $technicalName;
        $this->shopwareVersion = $shopwareVersion;
        $this->domain = $domain;
        $this->token = $token;
    }

    /**
     * @return string
     */
    public function getTechnicalName()
    {
        return $this->technicalName;
    }

    /**
     * @return string
     */
    public function getShopwareVersion()
    {
        return $this->shopwareVersion;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return AccessTokenStruct|null
     */
    public function getToken()
    {
        return $this->token;
    }
}
