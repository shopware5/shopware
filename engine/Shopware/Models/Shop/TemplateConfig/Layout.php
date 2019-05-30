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

namespace Shopware\Models\Shop\TemplateConfig;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Table(name="s_core_templates_config_layout")
 * @ORM\Entity()
 */
class Layout extends ModelEntity
{
    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    protected $name;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", nullable=false)
     */
    protected $type;

    /**
     * @var int
     *
     * @ORM\Column(name="template_id", type="integer", nullable=false)
     */
    protected $templateId;

    /**
     * @var \Shopware\Models\Shop\Template
     *
     * @ORM\ManyToOne(
     *     targetEntity="Shopware\Models\Shop\Template",
     *     inversedBy="layouts"
     * )
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * @var int
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    protected $parentId;

    /**
     * @var Layout
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\TemplateConfig\Layout", inversedBy="children")
     * @ORM\JoinColumn(name="parent_id", nullable=true, referencedColumnName="id")
     */
    protected $parent;

    /**
     * @var string
     *
     * @ORM\Column()
     */
    protected $title;

    /**
     * @var string[]
     *
     * @ORM\Column(name="attributes", type="array", nullable=false)
     */
    protected $attributes;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<Layout>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Shop\TemplateConfig\Layout", mappedBy="parent"))
     */
    protected $children;

    /**
     * @var ArrayCollection<Element>
     * @ORM\OneToMany(targetEntity="Element", mappedBy="container"))
     */
    protected $elements;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    public function __construct()
    {
        $this->children = new ArrayCollection();
        $this->elements = new ArrayCollection();
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $children
     */
    public function setChildren($children)
    {
        $this->children = $children;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Shopware\Models\Shop\TemplateConfig\Layout $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
    }

    /**
     * @return \Shopware\Models\Shop\TemplateConfig\Layout
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param \Shopware\Models\Shop\Template $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return \Shopware\Models\Shop\Template
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $type
     */
    public function setType($type)
    {
        $this->type = $type;
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $elements
     */
    public function setElements($elements)
    {
        $this->elements = $elements;
    }

    /**
     * @return string[]
     */
    public function getAttributes()
    {
        return $this->attributes;
    }

    /**
     * @param string[] $attributes
     */
    public function setAttributes($attributes)
    {
        $this->attributes = $attributes;
    }
}
