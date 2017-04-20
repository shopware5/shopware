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

class BasketStruct implements \JsonSerializable
{
    /**
     * @var DomainStruct[]
     */
    private $domains;

    /**
     * @var AddressStruct
     */
    private $address;

    /**
     * @var BasketPositionStruct[]
     */
    private $positions;

    /**
     * @var string
     */
    private $licenceDomain;

    /**
     * @var float
     */
    private $netPrice;

    /**
     * @var float
     */
    private $grossPrice;

    /**
     * @var float
     */
    private $taxRate;

    /**
     * @var float
     */
    private $taxPrice;

    /**
     * @param DomainStruct[]         $domains
     * @param AddressStruct          $address
     * @param BasketPositionStruct[] $positions
     * @param float                  $netPrice
     * @param float                  $grossPrice
     * @param float                  $taxRate
     * @param float                  $taxPrice
     */
    public function __construct($domains, $address, $positions, $netPrice, $grossPrice, $taxRate, $taxPrice)
    {
        $this->domains = $domains;
        $this->address = $address;
        $this->positions = $positions;
        $this->netPrice = $netPrice;
        $this->grossPrice = $grossPrice;
        $this->taxRate = $taxRate;
        $this->taxPrice = $taxPrice;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return DomainStruct[]
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @return AddressStruct
     */
    public function getAddress()
    {
        return $this->address;
    }

    /**
     * @return BasketPositionStruct[]
     */
    public function getPositions()
    {
        return $this->positions;
    }

    /**
     * @return float
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @return float
     */
    public function getGrossPrice()
    {
        return $this->grossPrice;
    }

    /**
     * @return float
     */
    public function getTaxRate()
    {
        return $this->taxRate;
    }

    /**
     * @return float
     */
    public function getTaxPrice()
    {
        return $this->taxPrice;
    }

    /**
     * @return string
     */
    public function getLicenceDomain()
    {
        return $this->licenceDomain;
    }

    /**
     * @param string $licenceDomain
     */
    public function setLicenceDomain($licenceDomain)
    {
        $this->licenceDomain = $licenceDomain;
    }
}
