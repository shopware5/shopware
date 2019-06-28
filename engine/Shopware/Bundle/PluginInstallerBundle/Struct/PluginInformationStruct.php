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

class PluginInformationStruct implements \JsonSerializable
{
    /**
     * @const array
     */
    const TYPE_MAPPING = [
        'buy' => 1,
        'rent' => 2,
        'test' => 3,
    ];

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $technicalName;

    /**
     * @var int
     */
    private $source;

    /**
     * @var int
     */
    private $type;

    /**
     * @var string
     */
    private $licenseCreation;

    /**
     * @var string
     */
    private $licenseExpiration;

    /**
     * @var bool
     */
    private $unknownLicense = false;

    /**
     * @var string
     */
    private $version;

    /**
     * @var string
     */
    private $subscriptionExpiration;

    /**
     * @var bool
     */
    private $wrongSubscription = false;

    /**
     * @var bool
     */
    private $subscriptionUpgradeRequired = false;

    /**
     * @var int
     */
    private $licenseQuantity;

    public function __construct(array $data)
    {
        $this->label = $data['label'];
        $this->technicalName = $data['name'];
        $this->type = self::TYPE_MAPPING[$data['type']['name']];
        $this->source = 0;
        $this->version = $data['usedVersion'];
        $this->licenseCreation = $data['licenseCreation'];
        $this->licenseExpiration = $data['licenseExpiration'];
        $this->unknownLicense = $data['unknownLicense'];
        $this->subscriptionExpiration = $data['subscriptionExpiration'];
        $this->wrongSubscription = $data['invalidVersionForSubscription'];
        $this->subscriptionUpgradeRequired = $data['pluginSubscriptionUpgradeRequired'];
        $this->licenseQuantity = isset($data['licenseQuantity']) ? (int) $data['licenseQuantity'] : 1;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return string
     */
    public function getTechnicalName()
    {
        return $this->technicalName;
    }

    /**
     * @return int
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return string
     */
    public function getLicenseCreation()
    {
        return $this->licenseCreation;
    }

    /**
     * @return string
     */
    public function getLicenseExpiration()
    {
        return $this->licenseExpiration;
    }

    /**
     * @return bool
     */
    public function isUnknownLicense()
    {
        return $this->unknownLicense;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @return string
     */
    public function getSubscriptionExpiration()
    {
        return $this->subscriptionExpiration;
    }

    /**
     * @return bool
     */
    public function isWrongSubscription()
    {
        return $this->wrongSubscription;
    }

    /**
     * @return bool
     */
    public function isSubscriptionUpgradeRequired()
    {
        return $this->subscriptionUpgradeRequired;
    }

    /**
     * @return int
     */
    public function getLicenseQuantity()
    {
        return $this->licenseQuantity;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
