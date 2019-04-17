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

namespace Shopware\Models\Shop;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Shop\TemplateConfig\Layout;
use Shopware\Models\Shop\TemplateConfig\Set;

/**
 * Template Model Entity
 *
 * @ORM\Table(name="s_core_templates")
 * @ORM\Entity()
 */
class Template extends ModelEntity
{
    /**
     * @var \Shopware\Models\Shop\Template
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Shop\Template")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent = null;

    /**
     * @var ArrayCollection<\Shopware\Models\Shop\Shop>
     *
     * @ORM\OneToMany(
     *     targetEntity="Shopware\Models\Shop\Shop",
     *     mappedBy="template"
     * )
     */
    protected $shops;

    /**
     * @var ArrayCollection<\Shopware\Models\Shop\TemplateConfig\Element>
     *
     * @ORM\OneToMany(
     *     targetEntity="Shopware\Models\Shop\TemplateConfig\Element",
     *     mappedBy="template",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    protected $elements;

    /**
     * @var ArrayCollection<\Shopware\Models\Shop\TemplateConfig\Layout>
     *
     * @ORM\OneToMany(
     *     targetEntity="Shopware\Models\Shop\TemplateConfig\Layout",
     *     mappedBy="template",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    protected $layouts;

    /**
     * @var ArrayCollection<\Shopware\Models\Shop\TemplateConfig\Set>
     *
     * @ORM\OneToMany(
     *     targetEntity="Shopware\Models\Shop\TemplateConfig\Set",
     *     mappedBy="template",
     *     orphanRemoval=true,
     *     cascade={"persist"}
     * )
     */
    protected $configSets;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Name of template in filesystem
     *
     * @var string
     *
     * @ORM\Column(name="template", type="string", length=255, nullable=false)
     */
    private $template;

    /**
     * Human readable name of the template
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Description of the template
     *
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * Author of the template
     *
     * @var string
     *
     * @ORM\Column(name="author", type="string", length=255, nullable=true)
     */
    private $author;

    /**
     * License of the template e.G. BSD / MIT / GPL
     *
     * @var string
     *
     * @ORM\Column(name="license", type="string", length=255, nullable=true)
     */
    private $license;

    /**
     * Whether or not this template support Edge Side Includes (ESI)
     *
     * @var bool
     *
     * @ORM\Column(name="esi", type="boolean")
     */
    private $esi = false;

    /**
     * Whether or not this template is Style Assist compatible
     *
     * @var bool
     *
     * @ORM\Column(name="style_support", type="boolean")
     */
    private $style = false;

    /**
     * Whether or not this template is EMOTIONS compatible
     *
     * @var bool
     *
     * @ORM\Column(name="emotion", type="boolean")
     */
    private $emotion = false;

    /**
     * @var int
     *
     * @ORM\Column(name="version", type="integer")
     */
    private $version = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="plugin_id", type="integer", nullable=true)
     */
    private $pluginId;

    /**
     * @var \Shopware\Models\Plugin\Plugin
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Plugin\Plugin", inversedBy="templates")
     * @ORM\JoinColumn(name="plugin_id", referencedColumnName="id")
     */
    private $plugin;

    /**
     * @var int
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId;

    public function __construct()
    {
        $this->shops = new ArrayCollection();
        $this->elements = new ArrayCollection();
        $this->layouts = new ArrayCollection();
        $this->configSets = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return Template
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * @param string $template
     *
     * @return Template
     */
    public function setTemplate($template)
    {
        $this->template = $template;

        return $this;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $description
     *
     * @return Template
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $license
     *
     * @return Template
     */
    public function setLicense($license)
    {
        $this->license = $license;

        return $this;
    }

    /**
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * @param bool $esi
     *
     * @return Template
     */
    public function setEsi($esi)
    {
        $this->esi = (bool) $esi;

        return $this;
    }

    /**
     * @return bool
     */
    public function getEsi()
    {
        return $this->esi;
    }

    /**
     * @param bool $emotion
     *
     * @return Template
     */
    public function setEmotion($emotion)
    {
        $this->emotion = (bool) $emotion;

        return $this;
    }

    /**
     * @param bool $emotion
     *
     * @return bool
     */
    public function getEmotion($emotion)
    {
        return $this->emotion;
    }

    /**
     * @param bool $style
     *
     * @return Template
     */
    public function setStyle($style)
    {
        $this->style = (bool) $style;

        return $this;
    }

    /**
     * Returns whether or not this template is Style Assist compatible
     *
     * @return bool
     */
    public function getStyle()
    {
        return $this->style;
    }

    /**
     * @return int
     */
    public function getVersion()
    {
        return $this->version;
    }

    /**
     * @param int $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return \Shopware\Models\Plugin\Plugin|null
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @param \Shopware\Models\Plugin\Plugin $plugin
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return string
     */
    public function toString()
    {
        return $this->getTemplate();
    }

    /**
     * @param \Shopware\Models\Shop\Template|null $parent
     */
    public function setParent(Template $parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return \Shopware\Models\Shop\Template|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param \Shopware\Models\Shop\TemplateConfig\Element[]|null $elements
     */
    public function setElements($elements)
    {
        $this->setOneToMany(
            $elements,
            \Shopware\Models\Shop\TemplateConfig\Element::class,
            'elements',
            'template'
        );
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Shop\TemplateConfig\Element>
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param ArrayCollection<\Shopware\Models\Shop\Shop> $shops
     */
    public function setShops($shops)
    {
        $this->shops = $shops;
    }

    /**
     * @return ArrayCollection<\Shopware\Models\Shop\Shop>
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param Layout[]|null $layouts
     */
    public function setLayouts($layouts)
    {
        $this->setOneToMany(
            $layouts,
            \Shopware\Models\Shop\TemplateConfig\Layout::class,
            'layouts',
            'template'
        );
    }

    /**
     * @return ArrayCollection<Layout>
     */
    public function getLayouts()
    {
        return $this->layouts;
    }

    /**
     * @return ArrayCollection<Set>
     */
    public function getConfigSets()
    {
        return $this->configSets;
    }

    /**
     * @param ArrayCollection<Set> $configSets
     */
    public function setConfigSets($configSets)
    {
        $this->configSets = $configSets;
    }
}
