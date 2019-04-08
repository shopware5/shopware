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

namespace Shopware\Models\Site;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\Site as SiteAttribute;

/**
 * Shopware Model Site
 *
 * This is the model for the Site module, which represents a single site of the shop.
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_cms_static")
 */
class Site extends ModelEntity
{
    /**
     * INVERSE SIDE
     *
     * @var SiteAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Site", mappedBy="site", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = true;

    /**
     * @var string
     *
     * @ORM\Column(name="tpl1variable", type="string", nullable=false)
     */
    private $tpl1variable;

    /**
     * @var string
     *
     * @ORM\Column(name="tpl1path", type="string", nullable=false)
     */
    private $tpl1path;

    /**
     * @var string
     *
     * @ORM\Column(name="tpl2variable", type="string", nullable=false)
     */
    private $tpl2variable;

    /**
     * @var string
     *
     * @ORM\Column(name="tpl2path", type="string", nullable=false)
     */
    private $tpl2path;

    /**
     * @var string
     *
     * @ORM\Column(name="tpl3variable", type="string", nullable=false)
     */
    private $tpl3variable;

    /**
     * @var string
     *
     * @ORM\Column(name="tpl3path", type="string", nullable=false)
     */
    private $tpl3path;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var string
     *
     * @ORM\Column(name="page_title", type="string", nullable=false)
     */
    private $pageTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_keywords", type="string", nullable=false)
     */
    private $metaKeywords;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="string", nullable=false)
     */
    private $metaDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="html", type="string", nullable=false)
     */
    private $html;

    /**
     * @var string
     *
     * @ORM\Column(name="`grouping`", type="string", nullable=false)
     */
    private $grouping;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="link", type="string", nullable=false)
     */
    private $link;

    /**
     * @var string
     *
     * @ORM\Column(name="target", type="string", nullable=false)
     */
    private $target;

    /**
     * @var string
     *
     * @ORM\Column(name="shop_ids", type="string", nullable=false)
     */
    private $shopIds;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="changed", type="datetime", nullable=false)
     */
    private $changed;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<Site>
     *
     * @ORM\OneToMany(targetEntity="Site", mappedBy="parent")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $children;

    /**
     * The parent category
     *
     * @var Site
     *
     * @ORM\ManyToOne(targetEntity="Site", inversedBy="children")
     * @ORM\JoinColumn(name="parentID", referencedColumnName="id")
     */
    private $parent;

    /**
     * @var int
     *
     * @ORM\Column(name="parentID", type="integer", nullable=true)
     */
    private $parentId;

    public function __construct()
    {
        $this->changed = new \DateTime();
    }

    /**
     * Returns the primary-key id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @param string $tpl1variable
     *
     * @return Site
     */
    public function setTpl1Variable($tpl1variable)
    {
        $this->tpl1variable = $tpl1variable;

        return $this;
    }

    /**
     * @return string
     */
    public function getTpl1Variable()
    {
        return $this->tpl1variable;
    }

    /**
     * @param string $tpl2variable
     *
     * @return Site
     */
    public function setTpl2Variable($tpl2variable)
    {
        $this->tpl2variable = $tpl2variable;

        return $this;
    }

    /**
     * @return string
     */
    public function getTpl2Variable()
    {
        return $this->tpl2variable;
    }

    /**
     * @param string $tpl3variable
     *
     * @return Site
     */
    public function setTpl3Variable($tpl3variable)
    {
        $this->tpl3variable = $tpl3variable;

        return $this;
    }

    /**
     * @return string
     */
    public function getTpl3Variable()
    {
        return $this->tpl3variable;
    }

    /**
     * @param string $tpl1path
     *
     * @return Site
     */
    public function setTpl1Path($tpl1path)
    {
        $this->tpl1path = $tpl1path;

        return $this;
    }

    /**
     * @return string
     */
    public function getTpl1Path()
    {
        return $this->tpl1path;
    }

    /**
     * @param string $tpl2path
     *
     * @return Site
     */
    public function setTpl2Path($tpl2path)
    {
        $this->tpl2path = $tpl2path;

        return $this;
    }

    /**
     * @return string
     */
    public function getTpl2Path()
    {
        return $this->tpl2path;
    }

    /**
     * @param string $tpl3path
     *
     * @return Site
     */
    public function setTpl3Path($tpl3path)
    {
        $this->tpl3path = $tpl3path;

        return $this;
    }

    /**
     * @return string
     */
    public function getTpl3Path()
    {
        return $this->tpl3path;
    }

    /**
     * @param string $description
     *
     * @return Site
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
     * @param string $html
     *
     * @return Site
     */
    public function setHtml($html)
    {
        $this->html = $html;

        return $this;
    }

    /**
     * @return string
     */
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @param string $grouping
     *
     * @return Site
     */
    public function setGrouping($grouping)
    {
        $this->grouping = $grouping;

        return $this;
    }

    /**
     * @return string
     */
    public function getGrouping()
    {
        return $this->grouping;
    }

    /**
     * @param int $position
     *
     * @return Site
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $link
     *
     * @return Site
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param string $target
     *
     * @return Site
     */
    public function setTarget($target)
    {
        $this->target = $target;

        return $this;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @return Site
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param Site $parent
     *
     * @return Site
     */
    public function setParent($parent)
    {
        $this->parent = $parent;

        return $this;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $children
     *
     * @return Site
     */
    public function setChildren($children)
    {
        $this->children = $children;

        return $this;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $parentId
     *
     * @return Site
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;

        return $this;
    }

    /**
     * @return SiteAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param SiteAttribute|array|null $attribute
     *
     * @return Site
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, SiteAttribute::class, 'attribute', 'site');
    }

    /**
     * @param string $metaDescription
     *
     * @return Site
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param string $metaKeywords
     *
     * @return Site
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;

        return $this;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @param string $pageTitle
     *
     * @return Site
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;

        return $this;
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * @param \DateTimeInterface|string $changed
     *
     * @return Site
     */
    public function setChanged($changed = 'now')
    {
        if (!$changed instanceof \DateTimeInterface) {
            $this->changed = new \DateTime($changed);
        } else {
            $this->changed = $changed;
        }

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getChanged()
    {
        return $this->changed;
    }

    /**
     * Returns the unexploded shop ids string (ex: |1|2|)
     *
     * @return string
     */
    public function getShopIds()
    {
        return $this->shopIds;
    }

    /**
     * Returns the exploded shop ids (ex: [1, 2])
     *
     * @return int[]
     */
    public function getExplodedShopIds()
    {
        if (empty($this->shopIds)) {
            return [];
        }

        $explodedShopIds = explode('|', trim($this->shopIds, '|'));

        // cast to ints
        $explodedShopIds = array_map(function ($elem) {
            return (int) $elem;
        }, $explodedShopIds);

        return $explodedShopIds;
    }

    /**
     * Set the unexploded shop ids string (ex: |1|2|)
     *
     * @param string $shopIds
     *
     * @return Site
     */
    public function setShopIds($shopIds)
    {
        $this->shopIds = $shopIds;

        return $this;
    }
}
