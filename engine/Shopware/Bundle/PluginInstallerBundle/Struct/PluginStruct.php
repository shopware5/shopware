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
 * Class PluginStruct
 * @package Shopware\Bundle\PluginInstallerBundle\Struct
 */
class PluginStruct implements \JsonSerializable
{
    /**
     * @var int
     * @unique
     * @optional
     */
    private $id;

    /**
     * @var string
     * @unique
     * @required
     */
    private $technicalName;

    /**
     * @var int
     */
    private $formId;

    /**
     * @var string
     */
    private $code;

    /**
     * @var string
     */
    private $source;

    /**
     * @var string
     */
    private $label;

    /**
     * @var boolean
     */
    private $active = false;

    /**
     * @var string
     */
    private $version = null;

    /**
     * @var string
     */
    private $exampleUrl;

    /**
     * @var \DateTime
     */
    private $installationDate = null;

    /**
     * @var \DateTime
     */
    private $updateDate = null;

    /**
     * @var boolean
     */
    private $updateAvailable = false;

    /**
     * @var string
     */
    private $availableVersion = null;

    /**
     * @var boolean
     */
    private $capabilityUpdate = false;

    /**
     * @var boolean
     */
    private $capabilityInstall = false;

    /**
     * @var boolean
     */
    private $capabilitySecureUninstall = false;

    /**
     * @var boolean
     */
    private $capabilityDummy = false;

    /**
     * @var boolean
     */
    private $capabilityActivate = false;

    /**
     * @var bool
     */
    private $freeDownload = false;

    /**
     * @var string
     */
    private $description;

    /**
     * @var string
     */
    private $localDescription;

    /**
     * @var string
     */
    private $contactForm;

    /**
     * @var int
     */
    private $rating;

    /**
     * Flag if the plugin can only bought over producer contact
     *
     * @var bool
     */
    private $useContactForm;

    /**
     * @var string
     */
    private $installationManual;

    /**
     * @var string
     */
    private $changelog;

    /**
     * @var PriceStruct[]
     */
    private $prices = [];

    /**
     * @var CommentStruct[]
     */
    private $comments = [];

    /**
     * @var PictureStruct[]
     */
    private $pictures = [];

    /**
     * @var string
     */
    private $iconPath;

    /**
     * @var string
     */
    private $localIcon;

    /**
     * @var ProducerStruct
     */
    private $producer;

    /**
     * @var boolean
     */
    private $encrypted = false;

    /**
     * @var bool
     */
    private $certified = false;

    /**
     * @var bool
     */
    private $licenceCheck = false;

    /**
     * @var string[]
     */
    private $addons;

    /**
     * @var LicenceStruct
     */
    private $licence;

    /**
     * @var bool
     */
    private $localUpdateAvailable = false;

    /**
     * @var string
     */
    private $link;

    /**
     * @var bool
     */
    private $redirectToStore = false;

    /**
     * @var float
     */
    private $lowestPrice;

    /**
     * @param string $technicalName
     */
    public function __construct($technicalName)
    {
        $this->technicalName = $technicalName;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return boolean
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return \DateTime
     */
    public function getInstallationDate()
    {
        return $this->installationDate;
    }

    /**
     * @param \DateTime $installationDate
     */
    public function setInstallationDate($installationDate)
    {
        $this->installationDate = $installationDate;
    }

    /**
     * @return PriceStruct[]
     */
    public function getPrices()
    {
        return $this->prices;
    }

    /**
     * @param PriceStruct[] $prices
     */
    public function setPrices($prices)
    {
        $this->prices = $prices;
    }

    /**
     * @return CommentStruct[]
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param CommentStruct[] $comments
     */
    public function setComments($comments)
    {
        $this->comments = $comments;
    }

    /**
     * @return string
     */
    public function getContactForm()
    {
        return $this->contactForm;
    }

    /**
     * @param string $contactForm
     */
    public function setContactForm($contactForm)
    {
        $this->contactForm = $contactForm;
    }

    /**
     * @return string
     */
    public function getInstallationManual()
    {
        return $this->installationManual;
    }

    /**
     * @param string $installationManual
     */
    public function setInstallationManual($installationManual)
    {
        $this->installationManual = $installationManual;
    }

    /**
     * @inheritdoc
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }

    /**
     * @return PictureStruct[]
     */
    public function getPictures()
    {
        return $this->pictures;
    }

    /**
     * @param PictureStruct[] $pictures
     */
    public function setPictures($pictures)
    {
        $this->pictures = $pictures;
    }

    /**
     * @return ProducerStruct
     */
    public function getProducer()
    {
        return $this->producer;
    }

    /**
     * @param ProducerStruct $producer
     */
    public function setProducer($producer)
    {
        $this->producer = $producer;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return boolean
     */
    public function hasCapabilityUpdate()
    {
        return $this->capabilityUpdate;
    }

    /**
     * @param boolean $capabilityUpdate
     */
    public function setCapabilityUpdate($capabilityUpdate)
    {
        $this->capabilityUpdate = $capabilityUpdate;
    }

    /**
     * @return boolean
     */
    public function hasCapabilityInstall()
    {
        return $this->capabilityInstall;
    }

    /**
     * @param boolean $capabilityInstall
     */
    public function setCapabilityInstall($capabilityInstall)
    {
        $this->capabilityInstall = $capabilityInstall;
    }

    /**
     * @return boolean
     */
    public function hasCapabilityDummy()
    {
        return $this->capabilityDummy;
    }

    /**
     * @param boolean $capabilityDummy
     */
    public function setCapabilityDummy($capabilityDummy)
    {
        $this->capabilityDummy = $capabilityDummy;
    }

    /**
     * @return boolean
     */
    public function hasCapabilityActivate()
    {
        return $this->capabilityActivate;
    }

    /**
     * @param boolean $capabilityActivate
     */
    public function setCapabilityActivate($capabilityActivate)
    {
        $this->capabilityActivate = $capabilityActivate;
    }

    /**
     * @return boolean
     */
    public function isUpdateAvailable()
    {
        return $this->updateAvailable;
    }

    /**
     * @param boolean $updateAvailable
     */
    public function setUpdateAvailable($updateAvailable)
    {
        $this->updateAvailable = $updateAvailable;
    }

    /**
     * @return string
     */
    public function getAvailableVersion()
    {
        return $this->availableVersion;
    }

    /**
     * @param string $availableVersion
     */
    public function setAvailableVersion($availableVersion)
    {
        $this->availableVersion = $availableVersion;
    }

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
     * @return string
     */
    public function getExampleUrl()
    {
        return $this->exampleUrl;
    }

    /**
     * @param string $exampleUrl
     */
    public function setExampleUrl($exampleUrl)
    {
        $this->exampleUrl = $exampleUrl;
    }

    /**
     * @return boolean
     */
    public function useContactForm()
    {
        return $this->useContactForm;
    }

    /**
     * @param boolean $useContactForm
     */
    public function setUseContactForm($useContactForm)
    {
        $this->useContactForm = $useContactForm;
    }

    /**
     * @return int
     */
    public function getRating()
    {
        return $this->rating;
    }

    /**
     * @param int $rating
     */
    public function setRating($rating)
    {
        $this->rating = $rating;
    }

    /**
     * @return string
     */
    public function getChangelog()
    {
        return $this->changelog;
    }

    /**
     * @param string $changelog
     */
    public function setChangelog($changelog)
    {
        $this->changelog = $changelog;
    }

    /**
     * @return string
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param string $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param string $code
     */
    public function setCode($code)
    {
        $this->code = $code;
    }

    /**
     * @return int
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * @param int $formId
     */
    public function setFormId($formId)
    {
        $this->formId = $formId;
    }

    /**
     * @return boolean
     */
    public function hasFreeDownload()
    {
        return $this->freeDownload;
    }

    /**
     * @param boolean $freeDownload
     */
    public function setFreeDownload($freeDownload)
    {
        $this->freeDownload = $freeDownload;
    }

    /**
     * @return \DateTime
     */
    public function getUpdateDate()
    {
        return $this->updateDate;
    }

    /**
     * @param \DateTime $updateDate
     */
    public function setUpdateDate($updateDate)
    {
        $this->updateDate = $updateDate;
    }

    /**
     * @return string
     */
    public function getLocalIcon()
    {
        return $this->localIcon;
    }

    /**
     * @param string $localIcon
     */
    public function setLocalIcon($localIcon)
    {
        $this->localIcon = $localIcon;
    }

    /**
     * @return \string[]
     */
    public function getAddons()
    {
        return $this->addons;
    }

    /**
     * @param \string[] $addons
     */
    public function setAddons($addons)
    {
        $this->addons = $addons;
    }

    /**
     * @return boolean
     */
    public function isEncrypted()
    {
        return $this->encrypted;
    }

    /**
     * @param boolean $encrypted
     */
    public function setEncrypted($encrypted)
    {
        $this->encrypted = $encrypted;
    }

    /**
     * @return boolean
     */
    public function hasLicenceCheck()
    {
        return $this->licenceCheck;
    }

    /**
     * @param boolean $licenceCheck
     */
    public function setLicenceCheck($licenceCheck)
    {
        $this->licenceCheck = $licenceCheck;
    }

    /**
     * @return boolean
     */
    public function isCertified()
    {
        return $this->certified;
    }

    /**
     * @param boolean $certified
     */
    public function setCertified($certified)
    {
        $this->certified = $certified;
    }

    /**
     * @return LicenceStruct
     */
    public function getLicence()
    {
        return $this->licence;
    }

    /**
     * @param LicenceStruct $licence
     */
    public function setLicence($licence)
    {
        $this->licence = $licence;
    }

    /**
     * @return boolean
     */
    public function hasCapabilitySecureUninstall()
    {
        return $this->capabilitySecureUninstall;
    }

    /**
     * @param boolean $capabilitySecureUninstall
     */
    public function setCapabilitySecureUninstall($capabilitySecureUninstall)
    {
        $this->capabilitySecureUninstall = $capabilitySecureUninstall;
    }

    /**
     * @return boolean
     */
    public function isLocalUpdateAvailable()
    {
        return $this->localUpdateAvailable;
    }

    /**
     * @param boolean $localUpdateAvailable
     */
    public function setLocalUpdateAvailable($localUpdateAvailable)
    {
        $this->localUpdateAvailable = $localUpdateAvailable;
    }

    /**
     * @return string
     */
    public function getLocalDescription()
    {
        return $this->localDescription;
    }

    /**
     * @param string $localDescription
     */
    public function setLocalDescription($localDescription)
    {
        $this->localDescription = $localDescription;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return boolean
     */
    public function isRedirectToStore()
    {
        return $this->redirectToStore;
    }

    /**
     * @param boolean $redirectToStore
     */
    public function setRedirectToStore($redirectToStore)
    {
        $this->redirectToStore = $redirectToStore;
    }

    /**
     * @return float
     */
    public function getLowestPrice()
    {
        return $this->lowestPrice;
    }

    /**
     * @param float $lowestPrice
     */
    public function setLowestPrice($lowestPrice)
    {
        $this->lowestPrice = $lowestPrice;
    }
}
