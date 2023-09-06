<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Models\Menu;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Plugin\Plugin;

/**
 * Shopware Model Menu
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_core_menu")
 */
class Menu extends ModelEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    public $label;

    /**
     * @var string|null
     *
     * @ORM\Column(name="onclick", type="string", length=255, nullable=true)
     */
    public $onclick;

    /**
     * @var string|null
     *
     * @ORM\Column(name="class", type="string", length=255, nullable=true)
     */
    public $class;

    /**
     * @var string|null
     *
     * @ORM\Column(name="controller", type="string", length=255, nullable=true)
     */
    public $controller;

    /**
     * @var string|null
     *
     * @ORM\Column(name="action", type="string", length=255, nullable=true)
     */
    public $action;

    /**
     * @var string|null
     *
     * @ORM\Column(name="shortcut", type="string", length=255, nullable=true)
     */
    public $shortcut;

    /**
     * @var Plugin|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Plugin\Plugin", inversedBy="menuItems")
     * @ORM\JoinColumn(name="pluginID", referencedColumnName="id")
     */
    protected $plugin;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 0;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = true;

    /**
     * @var int|null
     *
     * @ORM\Column(name="pluginID", type="integer", nullable=true)
     */
    private $pluginId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="parent", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * @var Menu|null
     *
     * @ORM\ManyToOne(targetEntity="Menu", inversedBy="children", cascade={"persist"})
     * @ORM\JoinColumn(name="parent", nullable=true, referencedColumnName="id", onDelete="SET NULL")
     */
    private $parent;

    /**
     * @var ArrayCollection<Menu>
     *
     * @ORM\OneToMany(targetEntity="Menu", mappedBy="parent", cascade={"all"}))
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $children;

    /**
     * @var string|null
     *
     * @ORM\Column(name="content_type", type="string", length=255, nullable=true)
     */
    private $contentType;

    public function __construct()
    {
        $this->children = new ArrayCollection();
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
     * @return string|null
     */
    public function getOnclick()
    {
        return $this->onclick;
    }

    /**
     * @param string|null $onclick
     */
    public function setOnclick($onclick)
    {
        $this->onclick = $onclick;
    }

    /**
     * @return string|null
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string|null $class
     */
    public function setClass($class)
    {
        $this->class = $class;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
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
     * @return int|null
     */
    public function getPluginId()
    {
        return $this->pluginId;
    }

    /**
     * @param int|null $pluginId
     */
    public function setPluginId($pluginId)
    {
        $this->pluginId = $pluginId;
    }

    /**
     * @return string|null
     */
    public function getController()
    {
        return $this->controller;
    }

    /**
     * @param string|null $controller
     */
    public function setController($controller)
    {
        $this->controller = $controller;
    }

    /**
     * @return string|null
     */
    public function getAction()
    {
        return $this->action;
    }

    /**
     * @param string|null $action
     */
    public function setAction($action)
    {
        $this->action = $action;
    }

    /**
     * @return string|null
     */
    public function getShortcut()
    {
        return $this->shortcut;
    }

    /**
     * @param string|null $shortcut
     */
    public function setShortcut($shortcut)
    {
        $this->shortcut = $shortcut;
    }

    /**
     * @return Menu|null
     */
    public function getParent()
    {
        return $this->parent;
    }

    public function setParent(?Menu $parent = null)
    {
        // Parent may be null when this menu item should be a main menu item
        if ($parent) {
            $parent->getChildren()->add($this);
        }
        $this->parent = $parent;
    }

    /**
     * @return ArrayCollection<Menu>
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param ArrayCollection<Menu> $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return bool
     */
    public function isVisible()
    {
        return $this->getActive();
    }

    /**
     * @return bool
     */
    public function hasChildren()
    {
        return \count($this->getChildren()) > 0;
    }

    /**
     * @return Plugin|null
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @param Plugin|null $plugin
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }

    public function setContentType(?string $contentType)
    {
        $this->contentType = $contentType;

        return $this;
    }
}
