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

namespace   Shopware\Models\Site;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

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
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $tpl1variable
     *
     * @ORM\Column(name="tpl1variable", type="string", nullable=false)
     */
    private $tpl1variable;

    /**
     * @var string $tpl1path
     *
     * @ORM\Column(name="tpl1path", type="string", nullable=false)
     */
    private $tpl1path;

    /**
     * @var string $tpl2variable
     *
     * @ORM\Column(name="tpl2variable", type="string", nullable=false)
     */
    private $tpl2variable;

    /**
     * @var string $tpl2path
     *
     * @ORM\Column(name="tpl2path", type="string", nullable=false)
     */
    private $tpl2path;

    /**
     * @var string $tpl3variable
     *
     * @ORM\Column(name="tpl3variable", type="string", nullable=false)
     */
    private $tpl3variable;

    /**
     * @var string $tpl3path
     *
     * @ORM\Column(name="tpl3path", type="string", nullable=false)
     */
    private $tpl3path;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var string $pageTitle
     *
     * @ORM\Column(name="page_title", type="string", nullable=false)
     */
    private $pageTitle;

    /**
     * @var string $metaKeywords
     *
     * @ORM\Column(name="meta_keywords", type="string", nullable=false)
     */
    private $metaKeywords;

    /**
     * @var string $metaDescription
     *
     * @ORM\Column(name="meta_description", type="string", nullable=false)
     */
    private $metaDescription;

    /**
     * @var string $html
     *
     * @ORM\Column(name="html", type="string", nullable=false)
     */
    private $html;

    /**
     * @var string $grouping
     *
     * @ORM\Column(name="grouping", type="string", nullable=false)
     */
    private $grouping;

    /**
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * @var string $link
     *
     * @ORM\Column(name="link", type="string", nullable=false)
     */
    private $link;

    /**
     * @var string $target
     *
     * @ORM\Column(name="target", type="string", nullable=false)
     */
    private $target;

    /**
     * @var string $shopIds
     *
     * @ORM\Column(name="shop_ids", type="string", nullable=false)
     */
    private $shopIds;

    /**
     * @var \DateTime $changed
     *
     * @ORM\Column(name="changed", type="datetime", nullable=false)
     */
    private $changed;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\OneToMany(targetEntity="Site", mappedBy="parent")
     * @ORM\OrderBy({"position" = "ASC"})
     */
    private $children;

    /**
     * The parent category
     *
     * @var Site $parent
     * @ORM\ManyToOne(targetEntity="Site", inversedBy="children")
     * @ORM\JoinColumn(name="parentID", referencedColumnName="id")
     */
    private $parent;

    /**
     * @var integer $parentId
     * @ORM\Column(name="parentID", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Site", mappedBy="site", orphanRemoval=true, cascade={"persist"})
     * @var \Shopware\Models\Attribute\Site
     */
    protected $attribute;

    public function __construct()
    {
        $this->changed = new \DateTime();
    }

    /**
     * Returns the primary-key id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $tpl1variable
     * @return string
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
     * @return string
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
     * @return string
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
     * @return string
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
     * @return string
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
     * @return string
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
     * @return string
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
     * @return string
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
     * @return string
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
     * @param integer $position
     * @return integer
     */
    public function setPosition($position)
    {
        $this->position = $position;
        return $this;
    }

    /**
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $link
     * @return string
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
     * @return string
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
     * @return \Shopware\Models\Site\Site
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param \Shopware\Models\Site\Site $parent
     */
    public function setParent($parent)
    {
        $this->parent = $parent;
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
     */
    public function setChildren($children)
    {
        $this->children = $children;
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
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return \Shopware\Models\Attribute\Site
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\Site|array|null $attribute
     * @return \Shopware\Models\Attribute\Site
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Site', 'attribute', 'site');
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
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
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
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
     */
    public function setPageTitle($pageTitle)
    {
        $this->pageTitle = $pageTitle;
    }

    /**
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
    }

    /**
     * Set changed
     *
     * @param \DateTime|string $changed
     * @return Site
     */
    public function setChanged($changed = 'now')
    {
        if (!$changed instanceof \DateTime) {
            $this->changed = new \DateTime($changed);
        } else {
            $this->changed = $changed;
        }
        return $this;
    }

    /**
     * Get changed
     *
     * @return \DateTime
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
            return array();
        }

        $explodedShopIds = explode('|', trim($this->shopIds, '|'));

        // cast to ints
        $explodedShopIds = array_map(function ($elem) {return (int) $elem;}, $explodedShopIds);

        return $explodedShopIds;
    }

    /**
     * @param string $shopIds
     */
    public function setShopIds($shopIds)
    {
        $this->shopIds = $shopIds;
    }
}
