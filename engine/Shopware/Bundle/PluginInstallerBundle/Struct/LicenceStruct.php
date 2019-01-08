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

class LicenceStruct implements \JsonSerializable
{
    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $technicalName;

    /**
     * @var string
     */
    private $iconPath;

    /**
     * @var string
     */
    private $shop;

    /**
     * @var string
     */
    private $subscription;

    /**
     * @var \DateTimeInterface
     */
    private $creationDate;

    /**
     * @var \DateTimeInterface
     */
    private $expirationDate;

    /**
     * @var string
     */
    private $licenseKey;

    /**
     * @var bool
     */
    private $isLicenseCheckEnabled = false;

    /**
     * @var PriceStruct
     */
    private $priceModel;

    /**
     * @var string
     */
    private $binaryLink;

    /**
     * @var string
     */
    private $binaryVersion;

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getTechnicalName()
    {
        return $this->technicalName;
    }

    /**
     * @param string $technicalName
     */
    public function setTechnicalName($technicalName)
    {
        $this->technicalName = $technicalName;
    }

    /**
     * @return string
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param string $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @return string
     */
    public function getSubscription()
    {
        return $this->subscription;
    }

    /**
     * @param string $subscription
     */
    public function setSubscription($subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreationDate()
    {
        return $this->creationDate;
    }

    /**
     * @param \DateTimeInterface $creationDate
     */
    public function setCreationDate($creationDate)
    {
        $this->creationDate = $creationDate;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getExpirationDate()
    {
        return $this->expirationDate;
    }

    /**
     * @param \DateTimeInterface $expirationDate
     */
    public function setExpirationDate($expirationDate)
    {
        $this->expirationDate = $expirationDate;
    }

    /**
     * @return string
     */
    public function getLicenseKey()
    {
        return $this->licenseKey;
    }

    /**
     * @param string $licenseKey
     */
    public function setLicenseKey($licenseKey)
    {
        $this->licenseKey = $licenseKey;
    }

    /**
     * @return PriceStruct
     */
    public function getPriceModel()
    {
        return $this->priceModel;
    }

    /**
     * @param PriceStruct $priceModel
     */
    public function setPriceModel($priceModel)
    {
        $this->priceModel = $priceModel;
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
    public function getBinaryLink()
    {
        return $this->binaryLink;
    }

    /**
     * @param string $binaryLink
     */
    public function setBinaryLink($binaryLink)
    {
        $this->binaryLink = $binaryLink;
    }

    /**
     * @return string
     */
    public function getBinaryVersion()
    {
        return $this->binaryVersion;
    }

    /**
     * @param string $binaryVersion
     */
    public function setBinaryVersion($binaryVersion)
    {
        $this->binaryVersion = $binaryVersion;
    }

    /**
     * @return string
     */
    public function getIconPath()
    {
        return $this->iconPath;
    }

    /**
     * @param string $iconPath
     */
    public function setIconPath($iconPath)
    {
        $this->iconPath = $iconPath;
    }

    /**
     * @return bool
     */
    public function isLicenseCheckEnabled()
    {
        return $this->isLicenseCheckEnabled;
    }

    /**
     * @param bool $isLicenseCheckEnabled
     */
    public function setIsLicenseCheckEnabled($isLicenseCheckEnabled)
    {
        $this->isLicenseCheckEnabled = $isLicenseCheckEnabled;
    }
}
