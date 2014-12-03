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
 * @package Shopware\Bundle\PluginInstallerBundle\Struct
 */
class DomainStruct implements \JsonSerializable
{
    /**
     * @var string
     */
    private $domain;

    /**
     * @var float
     */
    private $balance;

    /**
     * @var float
     */
    private $dispo;

    /**
     * @var boolean
     */
    private $isPartner;

    /**
     * @param string $domain
     * @param float $balance
     * @param float $dispo
     * @param $isPartner
     */
    public function __construct($domain, $balance, $dispo, $isPartner)
    {
        $this->domain = $domain;
        $this->balance = $balance;
        $this->dispo = $dispo;
        $this->isPartner = $isPartner;
    }

    /**
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * @return float
     */
    public function getBalance()
    {
        return $this->balance;
    }

    /**
     * @return float
     */
    public function getDispo()
    {
        return $this->dispo;
    }

    /**
     * @return boolean
     */
    public function isPartner()
    {
        return $this->isPartner;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
