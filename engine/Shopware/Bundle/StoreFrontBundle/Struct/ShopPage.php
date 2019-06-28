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

namespace Shopware\Bundle\StoreFrontBundle\Struct;

class ShopPage extends Extendable
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $tpl1variable;

    /**
     * @var string
     */
    protected $tpl1path;

    /**
     * @var string
     */
    protected $tpl2variable;

    /**
     * @var string
     */
    protected $tpl2path;

    /**
     * @var string
     */
    protected $tpl3variable;

    /**
     * @var string
     */
    protected $tpl3path;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $pageTitle;

    /**
     * @var string
     */
    protected $metaKeywords;

    /**
     * @var string
     */
    protected $metaDescription;

    /**
     * @var string
     */
    protected $html;

    /**
     * @var string[]
     */
    protected $grouping = [];

    /**
     * @var int
     */
    protected $position;

    /**
     * @var string
     */
    protected $link;

    /**
     * @var string
     */
    protected $target;

    /**
     * @var int[]
     */
    protected $shopIds;

    /**
     * @var Shop[]
     */
    protected $shops;

    /**
     * @var \DateTimeInterface
     */
    protected $changed;

    /**
     * @var ShopPage[]
     */
    protected $children = [];

    /**
     * @var int
     */
    protected $parentId;

    /**
     * @var ShopPage
     */
    protected $parent;

    /**
     * @var int
     */
    protected $childrenCount;

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
    public function getTpl1Variable()
    {
        return $this->tpl1variable;
    }

    /**
     * @param string $tpl1variable
     */
    public function setTpl1Variable($tpl1variable)
    {
        $this->tpl1variable = $tpl1variable;
    }

    /**
     * @return string
     */
    public function getTpl1Path()
    {
        return $this->tpl1path;
    }

    /**
     * @param string $tpl1path
     */
    public function setTpl1Path($tpl1path)
    {
        $this->tpl1path = $tpl1path;
    }

    /**
     * @return string
     */
    public function getTpl2Variable()
    {
        return $this->tpl2variable;
    }

    /**
     * @param string $tpl2variable
     */
    public function setTpl2Variable($tpl2variable)
    {
        $this->tpl2variable = $tpl2variable;
    }

    /**
     * @return string
     */
    public function getTpl2Path()
    {
        return $this->tpl2path;
    }

    /**
     * @param string $tpl2path
     */
    public function setTpl2Path($tpl2path)
    {
        $this->tpl2path = $tpl2path;
    }

    /**
     * @return string
     */
    public function getTpl3Variable()
    {
        return $this->tpl3variable;
    }

    /**
     * @param string $tpl3variable
     */
    public function setTpl3Variable($tpl3variable)
    {
        $this->tpl3variable = $tpl3variable;
    }

    /**
     * @return string
     */
    public function getTpl3Path()
    {
        return $this->tpl3path;
    }

    /**
     * @param string $tpl3path
     */
    public function setTpl3Path($tpl3path)
    {
        $this->tpl3path = $tpl3path;
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
     * @return string
     */
    public function getPageTitle()
    {
        return $this->pageTitle;
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
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
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
    public function getMetaDescription()
    {
        return $this->metaDescription;
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
    public function getHtml()
    {
        return $this->html;
    }

    /**
     * @param string $html
     */
    public function setHtml($html)
    {
        $this->html = $html;
    }

    /**
     * @return string[]
     */
    public function getGrouping()
    {
        return $this->grouping;
    }

    /**
     * @param string[] $grouping
     */
    public function setGrouping(array $grouping)
    {
        $this->grouping = $grouping;
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
        $this->position = (int) $position;
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
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return Shop[]
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param Shop[] $shops
     */
    public function setShops(array $shops)
    {
        $this->shops = $shops;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getChanged()
    {
        return $this->changed;
    }

    public function setChanged(\DateTimeInterface $changed)
    {
        $this->changed = $changed;
    }

    /**
     * @return ShopPage[]
     */
    public function getChildren()
    {
        return $this->children;
    }

    /**
     * @param ShopPage[] $children
     */
    public function setChildren(array $children)
    {
        $this->children = $children;
    }

    /**
     * @return ShopPage
     */
    public function getParent()
    {
        return $this->parent;
    }

    /**
     * @param ShopPage $parent
     */
    public function setParent($parent = null)
    {
        $this->parent = $parent;
    }

    /**
     * @return int[]
     */
    public function getShopIds()
    {
        return $this->shopIds;
    }

    /**
     * @param int[] $shopIds
     */
    public function setShopIds(array $shopIds)
    {
        $this->shopIds = $shopIds;
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
     * @return int
     */
    public function getChildrenCount()
    {
        return $this->childrenCount;
    }

    /**
     * @param int $childrenCount
     */
    public function setChildrenCount($childrenCount)
    {
        $this->childrenCount = $childrenCount;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
