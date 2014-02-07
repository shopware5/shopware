<?php
/**
 * Shopware 4
 * Copyright © shopware AG
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
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping as ORM;

/**
 * Template Model Entity
 *
 * @ORM\Table(name="s_core_templates")
 * @ORM\Entity
 */
class Template extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Name of template in filesystem
     *
     * @var string $template
     * @ORM\Column(name="template", type="string", length=255, nullable=false)
     */
    private $template;

    /**
     * Human readable name of the template
     *
     * @var string $name
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Description of the template
     *
     * @var string $description
     * @ORM\Column(name="description", type="string", length=255, nullable=false)
     */
    private $description;

    /**
     * Author of the template
     *
     * @var string $author
     * @ORM\Column(name="author", type="string", length=255, nullable=true)
     */
    private $author;

    /**
     * License of the template e.G. BSD / MIT / GPL
     *
     * @var string $license
     * @ORM\Column(name="license", type="string", length=255, nullable=true)
     */
    private $license;

    /**
     * Whether or not this template support Edge Side Includes (ESI)
     *
     * @var boolean $esi
     * @ORM\Column(name="esi", type="boolean")
     */
    private $esi = false;

    /**
     * Whether or not this template is Style Assist compatible
     *
     * @var boolean $style
     * @ORM\Column(name="style_support", type="boolean")
     */
    private $style = false;

    /**
     * Whether or not this template is EMOTIONS compatible
     *
     * @var boolean $emotion
     * @ORM\Column(name="emotion", type="boolean")
     */
    private $emotion = false;

    /**
     * @var string $version
     * @ORM\Column(name="version", type="integer")
     */
    private $version = 1;

    /**
     * @var integer $pluginId
     * @ORM\Column(name="plugin_id", type="integer", nullable=true)
     */
    private $pluginId;

    /**
     * @var \Shopware\Models\Plugin\Plugin
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Plugin\Plugin", inversedBy="templates")
     * @ORM\JoinColumn(name="plugin_id", referencedColumnName="id")
     */
    private $plugin;

    /**
     * @var integer $parentId
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId = null;

    /**
     * @var \Shopware\Models\Shop\Template
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Shop\Template")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    protected $parent = null;

    /**
     * @var ArrayCollection $shops
     * @ORM\OneToMany(targetEntity="Shop", mappedBy="template")
     */
    protected $shops;

    function __construct()
    {
        $this->shops = new ArrayCollection();
    }


    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param string $name
     * @return \Shopware\Models\Snippet\Snippet
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
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
     * @param boolean $esi
     * @return Template
     */
    public function setEsi($esi)
    {
        $this->esi = (bool) $esi;
        return $this;
    }

    /**
     * @return boolean
     */
    public function getEsi()
    {
        return $this->esi;
    }

    /**
     * @param boolean $emotion
     * @return Template
     */
    public function setEmotion($emotion)
    {
        $this->emotion = (bool) $emotion;
        return $this;
    }

    /**
     * @param boolean $emotion
     * @return Template
     */
    public function getEmotion($emotion)
    {
        return $this->emotion;
    }

    /**
     * @param boolean $style
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
     * @return boolean
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
     * @return \Shopware\Models\Plugin\Plugin
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
     * @param \Shopware\Models\Shop\Template $parent
     */
    public function setParent(Template $parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return \Shopware\Models\Shop\Template
     */
    public function getParent()
    {
        return $this->parent;
    }
}
