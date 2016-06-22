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

namespace Shopware\Models\Plugin;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * @category  Shopware
 * @package   Shopware\Models\Plugin
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 *
 * @ORM\Table(name="s_core_plugins")
 * @ORM\Entity
 */
class Plugin extends ModelEntity
{
    /**
     * @var integer
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="label", type="string", nullable=false)
     */
    private $label;

    /**
     * @var string
     * @ORM\Column(name="namespace", type="string", nullable=false)
     */
    private $namespace;

    /**
     * @var string
     * @ORM\Column(name="source", type="string", nullable=false)
     */
    private $source;

    /**
     * @var string
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var boolean
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = false;

    /**
     * @var \DateTime
     * @ORM\Column(name="added", type="datetime", nullable=false)
     */
    private $added;

    /**
     * @var \DateTime
     * @ORM\Column(name="installation_date", type="datetime", nullable=true)
     */
    private $installed;

    /**
     * @var \DateTime
     * @ORM\Column(name="update_date", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var \DateTime
     * @ORM\Column(name="refresh_date", type="datetime", nullable=true)
     */
    private $refreshed;

    /**
     * @var string $author
     * @ORM\Column(name="author", type="string", nullable=true)
     */
    private $author;

    /**
     * @var string
     * @ORM\Column(name="copyright", type="string", nullable=true)
     */
    private $copyright;

    /**
     * @var string
     * @ORM\Column(name="license", type="string", nullable=false)
     */
    private $license;

    /**
     * @var string
     * @ORM\Column(name="version", type="string", nullable=false)
     */
    private $version;

    /**
     * @var string
     * @ORM\Column(name="support", type="string", nullable=false)
     */
    private $support;

    /**
     * @var string
     * @ORM\Column(name="changes", type="string", nullable=false)
     */
    private $changes;

    /**
     * @var string
     * @ORM\Column(name="link", type="string", nullable=false)
     */
    private $link;

    /**
     * @var string
     * @ORM\Column(name="update_version", type="string", nullable=false)
     */
    private $updateVersion;

    /**
     * @var string
     * @ORM\Column(name="update_source", type="string", nullable=false)
     */
    private $updateSource;

    /**
     * @var boolean
     * @ORM\Column(name="capability_update", type="boolean")
     */
    private $capabilityUpdate = true;

    /**
     * @var boolean
     * @ORM\Column(name="capability_install", type="boolean")
     */
    private $capabilityInstall = true;

    /**
     * @var boolean
     * @ORM\Column(name="capability_enable", type="boolean")
     */
    private $capabilityEnable = true;

    /**
     * @var boolean
     * @ORM\Column(name="capability_secure_uninstall", type="boolean")
     */
    private $capabilitySecureUninstall = false;

    /**
     * INVERSE SIDE
     * @var \Shopware\Models\Config\Form[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Config\Form", mappedBy="plugin", cascade={"all"})
     * @ORM\JoinColumn(name="id", referencedColumnName="plugin_id")
     * @ORM\OrderBy({"position" = "ASC", "id" = "ASC"})
     */
    private $configForms;

    /**
     * INVERSE SIDE
     * @var \Shopware\Models\Menu\Menu[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Menu\Menu", mappedBy="plugin", cascade={"all"})
     * @ORM\JoinColumn(name="id", referencedColumnName="pluginID")
     * @ORM\OrderBy({"position" = "ASC", "id" = "ASC"})
     */
    private $menuItems;

    /**
     * INVERSE SIDE
     * @var \Shopware\Models\Payment\Payment[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Payment\Payment", mappedBy="plugin", cascade={"all"})
     * @ORM\JoinColumn(name="id", referencedColumnName="pluginID")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $payments;

    /**
     * INVERSE SIDE
     * @var \Shopware\Models\Shop\Template[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Shop\Template", mappedBy="plugin", cascade={"all"})
     * @ORM\JoinColumn(name="id", referencedColumnName="plugin_id")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $templates;

    /**
     * INVERSE SIDE
     * @var \Shopware\Models\Widget\Widget[]|ArrayCollection
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Widget\Widget", mappedBy="plugin", cascade={"all"})
     * @ORM\JoinColumn(name="id", referencedColumnName="plugin_id")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $widgets;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Shopware\Models\Plugin\License", mappedBy="plugin")
     * @ORM\OrderBy({"type" = "ASC"})
     */
    private $licenses;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\Library\Component", mappedBy="plugin", orphanRemoval=true, cascade={"all"})
     */
    protected $emotionComponents;

    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->added = new \DateTime('now');
        $this->emotionComponents = new ArrayCollection();
        $this->configForms = new ArrayCollection();
        $this->menuItems = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->templates = new ArrayCollection();
        $this->licenses = new ArrayCollection();
        $this->widgets = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
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
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param string $namespace
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
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
    public function getActive()
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
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @param \DateTime $added
     */
    public function setAdded($added)
    {
        $this->added = $added;
    }

    /**
     * @return \DateTime
     */
    public function getInstalled()
    {
        return $this->installed;
    }

    /**
     * @param \DateTime $installed
     */
    public function setInstalled($installed)
    {
        $this->installed = $installed;
    }

    /**
     * @return \DateTime
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param \DateTime $updated
     */
    public function setUpdated($updated)
    {
        $this->updated = $updated;
    }

    /**
     * @return string
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * @return string
     */
    public function getCopyright()
    {
        return $this->copyright;
    }

    /**
     * @param string $copyright
     */
    public function setCopyright($copyright)
    {
        $this->copyright = $copyright;
    }

    /**
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * @param string $license
     */
    public function setLicense($license)
    {
        $this->license = $license;
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
     * @return string
     */
    public function getSupport()
    {
        return $this->support;
    }

    /**
     * @param string $support
     */
    public function setSupport($support)
    {
        $this->support = $support;
    }

    /**
     * @return string
     */
    public function getChanges()
    {
        return $this->changes;
    }

    /**
     * @param string $changes
     */
    public function setChanges($changes)
    {
        $this->changes = $changes;
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
     * @return \Doctrine\Common\Collections\ArrayCollection|\Shopware\Models\Config\Form[]
     */
    public function getConfigForms()
    {
        return $this->configForms;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|\Shopware\Models\Menu\Menu[] $configForms
     */
    public function setConfigForms($configForms)
    {
        $this->configForms = $configForms;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Shopware\Models\Menu\Menu[]
     */
    public function getMenuItems()
    {
        return $this->menuItems;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|\Shopware\Models\Menu\Menu[] $menuItems
     */
    public function setMenuItems($menuItems)
    {
        $this->menuItems = $menuItems;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Shopware\Models\Payment\Payment[]
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|\Shopware\Models\Payment\Payment[] $payments
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection|\Shopware\Models\Shop\Template[]
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|\Shopware\Models\Shop\Template[] $templates
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }

    /**
     * @return
     */
    public function getLicenses()
    {
        return $this->licenses;
    }

    /**
     * @param  $licenses
     */
    public function setLicenses($licenses)
    {
        $this->licenses = $licenses;
    }

    /**
     * @return string
     */
    public function getUpdateVersion()
    {
        return $this->updateVersion;
    }

    /**
     * @param string $updateVersion
     */
    public function setUpdateVersion($updateVersion)
    {
        $this->updateVersion = $updateVersion;
    }

    /**
     * @return string
     */
    public function getUpdateSource()
    {
        return $this->updateSource;
    }

    /**
     * @param string $updateSource
     */
    public function setUpdateSource($updateSource)
    {
        $this->updateSource = $updateSource;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getEmotionComponents()
    {
        return $this->emotionComponents;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $emotionComponents
     */
    public function setEmotionComponents($emotionComponents)
    {
        $this->emotionComponents = $emotionComponents;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getWidgets()
    {
        return $this->widgets;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $widgets
     */
    public function setWidgets($widgets)
    {
        $this->widgets = $widgets;
    }

    /**
     * @return boolean
     */
    public function hasCapabilitySecureUninstall()
    {
        return $this->capabilitySecureUninstall;
    }

    /**
     * @return boolean
     */
    public function hasCapabilityEnable()
    {
        return $this->capabilityEnable;
    }

    /**
     * @return boolean
     */
    public function hasCapabilityInstall()
    {
        return $this->capabilityInstall;
    }

    /**
     * @return boolean
     */
    public function hasCapabilityUpdate()
    {
        return $this->capabilityUpdate;
    }

    /**
     * @return bool
     */
    public function isLegacyPlugin()
    {
        return $this->namespace !== 'ShopwarePlugins';
    }
}
