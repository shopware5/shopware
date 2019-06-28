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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Table(name="s_core_plugins")
 * @ORM\Entity()
 */
class Plugin extends ModelEntity
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Emotion\Library\Component>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\Library\Component", mappedBy="plugin", orphanRemoval=true, cascade={"all"})
     */
    protected $emotionComponents;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", nullable=false)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="namespace", type="string", nullable=false)
     */
    private $namespace;

    /**
     * @var string
     *
     * @ORM\Column(name="source", type="string", nullable=false)
     */
    private $source;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean")
     */
    private $active = false;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="added", type="datetime", nullable=false)
     */
    private $added;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="installation_date", type="datetime", nullable=true)
     */
    private $installed;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="update_date", type="datetime", nullable=true)
     */
    private $updated;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="refresh_date", type="datetime", nullable=true)
     */
    private $refreshed;

    /**
     * @var string
     *
     * @ORM\Column(name="author", type="string", nullable=true)
     */
    private $author;

    /**
     * @var string
     *
     * @ORM\Column(name="copyright", type="string", nullable=true)
     */
    private $copyright;

    /**
     * @var string
     *
     * @ORM\Column(name="license", type="string", nullable=false)
     */
    private $license;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", nullable=false)
     */
    private $version;

    /**
     * @var string
     *
     * @ORM\Column(name="support", type="string", nullable=false)
     */
    private $support;

    /**
     * @var string
     *
     * @ORM\Column(name="changes", type="string", nullable=false)
     */
    private $changes;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", nullable=false)
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(name="update_version", type="string", nullable=false)
     */
    private $updateVersion;

    /**
     * @var string
     *
     * @ORM\Column(name="update_source", type="string", nullable=false)
     */
    private $updateSource;

    /**
     * @var bool
     *
     * @ORM\Column(name="capability_update", type="boolean")
     */
    private $capabilityUpdate = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="capability_install", type="boolean")
     */
    private $capabilityInstall = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="capability_enable", type="boolean")
     */
    private $capabilityEnable = true;

    /**
     * @var bool
     *
     * @ORM\Column(name="capability_secure_uninstall", type="boolean")
     */
    private $capabilitySecureUninstall = false;

    /**
     * @var string
     *
     * @ORM\Column(name="translations", type="text")
     */
    private $translations;

    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Config\Form>
     *
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Config\Form", mappedBy="plugin", cascade={"all"})
     * @ORM\JoinColumn(name="id", referencedColumnName="plugin_id")
     * @ORM\OrderBy({"position" = "ASC", "id" = "ASC"})
     */
    private $configForms;

    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Menu\Menu>
     *
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Menu\Menu", mappedBy="plugin", cascade={"all"})
     * @ORM\JoinColumn(name="id", referencedColumnName="pluginID")
     * @ORM\OrderBy({"position" = "ASC", "id" = "ASC"})
     */
    private $menuItems;

    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Payment\Payment>
     *
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Payment\Payment", mappedBy="plugin", cascade={"all"})
     * @ORM\JoinColumn(name="id", referencedColumnName="pluginID")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $payments;

    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Shop\Template>
     *
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Shop\Template", mappedBy="plugin", cascade={"all"})
     * @ORM\JoinColumn(name="id", referencedColumnName="plugin_id")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $templates;

    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Widget\Widget>
     *
     * @ORM\OneToMany(targetEntity="\Shopware\Models\Widget\Widget", mappedBy="plugin", cascade={"all"})
     * @ORM\JoinColumn(name="id", referencedColumnName="plugin_id")
     * @ORM\OrderBy({"id" = "ASC"})
     */
    private $widgets;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Plugin\License>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Plugin\License", mappedBy="plugin")
     * @ORM\OrderBy({"type" = "ASC"})
     */
    private $licenses;

    /**
     * @var bool
     *
     * @ORM\Column(name="in_safe_mode", type="boolean")
     */
    private $inSafeMode = false;

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
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @param \DateTimeInterface $added
     */
    public function setAdded($added)
    {
        $this->added = $added;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getInstalled()
    {
        return $this->installed;
    }

    /**
     * @param \DateTimeInterface|null $installed
     */
    public function setInstalled($installed)
    {
        $this->installed = $installed;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * @param \DateTimeInterface $updated
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
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Config\Form>
     */
    public function getConfigForms()
    {
        return $this->configForms;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Config\Form> $configForms
     */
    public function setConfigForms($configForms)
    {
        $this->configForms = $configForms;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Menu\Menu>
     */
    public function getMenuItems()
    {
        return $this->menuItems;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Menu\Menu> $menuItems
     */
    public function setMenuItems($menuItems)
    {
        $this->menuItems = $menuItems;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Payment\Payment>
     */
    public function getPayments()
    {
        return $this->payments;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Payment\Payment> $payments
     */
    public function setPayments($payments)
    {
        $this->payments = $payments;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Shop\Template>
     */
    public function getTemplates()
    {
        return $this->templates;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Shop\Template> $templates
     */
    public function setTemplates($templates)
    {
        $this->templates = $templates;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Plugin\License>
     */
    public function getLicenses()
    {
        return $this->licenses;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Plugin\License> $licenses
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
     * @param string|null $updateVersion
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
     * @param string|null $updateSource
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
     * @return bool
     */
    public function hasCapabilitySecureUninstall()
    {
        return $this->capabilitySecureUninstall;
    }

    /**
     * @return bool
     */
    public function hasCapabilityEnable()
    {
        return $this->capabilityEnable;
    }

    /**
     * @return bool
     */
    public function hasCapabilityInstall()
    {
        return $this->capabilityInstall;
    }

    /**
     * @return bool
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
        return !in_array($this->namespace, ['ShopwarePlugins', 'ProjectPlugins'], true);
    }

    /**
     * @return string
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $translations
     */
    public function setTranslations($translations)
    {
        $this->translations = $translations;
    }

    /**
     * @return bool
     */
    public function isInSafeMode()
    {
        return $this->inSafeMode;
    }

    /**
     * @param bool $inSafeMode
     */
    public function setInSafeMode($inSafeMode)
    {
        $this->inSafeMode = $inSafeMode;
    }
}
