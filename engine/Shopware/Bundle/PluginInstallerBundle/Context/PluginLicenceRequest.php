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

/**
 * @deprecated in 5.6, will be removed in 5.7 without replacement
 */
class PluginLicenceRequest
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
     * @var AccessTokenStruct
     */
    private $token;

    /**
     * @param AccessTokenStruct $token
     * @param string            $domain
     * @param string            $shopwareVersion
     * @param string            $technicalName
     */
    public function __construct($token, $domain, $shopwareVersion, $technicalName)
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        $this->token = $token;
        $this->domain = $domain;
        $this->shopwareVersion = $shopwareVersion;
        $this->technicalName = $technicalName;
    }

    /**
     * @return string
     */
    public function getTechnicalName()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->technicalName;
    }

    /**
     * @return string
     */
    public function getShopwareVersion()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->shopwareVersion;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->domain;
    }

    /**
     * @return AccessTokenStruct
     */
    public function getToken()
    {
        trigger_error(sprintf('%s:%s is deprecated since Shopware 5.6 and will be removed with 5.7. Will be removed without replacement.', __CLASS__, __METHOD__), E_USER_DEPRECATED);

        return $this->token;
    }
}
