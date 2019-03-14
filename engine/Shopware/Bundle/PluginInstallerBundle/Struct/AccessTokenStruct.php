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

class AccessTokenStruct implements \JsonSerializable
{
    /**
     * @var string
     */
    private $token;

    /**
     * @var \DateTimeInterface
     */
    private $expire;

    /**
     * @var int
     */
    private $userId;

    /**
     * @var string
     */
    private $locale;

    /**
     * @var string
     */
    private $shopwareId;

    /**
     * @param string $token
     * @param string $shopwareId
     * @param int    $userId
     * @param string $locale
     */
    public function __construct(
        $token,
        \DateTimeInterface $expire,
        $shopwareId,
        $userId,
        $locale
    ) {
        $this->token = $token;
        $this->expire = $expire;
        $this->userId = $userId;
        $this->locale = $locale;
        $this->shopwareId = $shopwareId;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return string
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getExpire()
    {
        return $this->expire;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @return string
     */
    public function getShopwareId()
    {
        return $this->shopwareId;
    }
}
