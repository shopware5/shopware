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

namespace Shopware\Bundle\EmotionBundle\Struct;

use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;

class Emotion extends Extendable
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $cols;

    /**
     * @var int
     */
    protected $cellSpacing;

    /**
     * @var int
     */
    protected $cellHeight;

    /**
     * @var int
     */
    protected $articleHeight;

    /**
     * @var int
     */
    protected $rows;

    /**
     * @var \DateTimeInterface
     */
    protected $validFrom;

    /**
     * @var \DateTimeInterface
     */
    protected $validTo;

    /**
     * @var int
     */
    protected $userId;

    /**
     * @var bool
     */
    protected $showListing;

    /**
     * @var bool
     */
    protected $isLandingPage;

    /**
     * @var string
     */
    protected $seoTitle;

    /**
     * @var string
     */
    protected $seoKeywords;

    /**
     * @var string
     */
    protected $seoDescription;

    /**
     * @var \DateTimeInterface
     */
    protected $createDate;

    /**
     * @var \DateTimeInterface
     */
    protected $modifiedDate;

    /**
     * @var int
     */
    protected $templateId;

    /**
     * @var int[]
     */
    protected $devices;

    /**
     * @var bool
     */
    protected $fullscreen;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var int
     */
    protected $parentId;

    /**
     * @var bool
     */
    protected $isPreview = false;

    /**
     * @var string
     */
    protected $previewSecret;

    /**
     * @var Element[]
     */
    protected $elements = [];

    /**
     * @var int[]
     */
    protected $categoryIds = [];

    /**
     * @var Category[]
     */
    protected $categories = [];

    /**
     * @var EmotionTemplate|null
     */
    protected $template;

    /**
     * @var int[]
     */
    protected $shopIds = [];

    /**
     * @var Shop[]
     */
    protected $shops = [];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->active;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return int
     */
    public function getCols()
    {
        return $this->cols;
    }

    /**
     * @return int
     */
    public function getCellSpacing()
    {
        return $this->cellSpacing;
    }

    /**
     * @return int
     */
    public function getCellHeight()
    {
        return $this->cellHeight;
    }

    /**
     * @return int
     */
    public function getArticleHeight()
    {
        return $this->articleHeight;
    }

    /**
     * @return int
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->userId;
    }

    /**
     * @return bool
     */
    public function isShowListing()
    {
        return $this->showListing;
    }

    /**
     * @return bool
     */
    public function isLandingPage()
    {
        return $this->isLandingPage;
    }

    /**
     * @return string
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @return string
     */
    public function getSeoKeywords()
    {
        return $this->seoKeywords;
    }

    /**
     * @return string
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getModifiedDate()
    {
        return $this->modifiedDate;
    }

    /**
     * @return int
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * @return int[]
     */
    public function getDevices()
    {
        return $this->devices;
    }

    /**
     * @return bool
     */
    public function isFullscreen()
    {
        return $this->fullscreen;
    }

    /**
     * @return string
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param int $cols
     */
    public function setCols($cols)
    {
        $this->cols = $cols;
    }

    /**
     * @param int $cellSpacing
     */
    public function setCellSpacing($cellSpacing)
    {
        $this->cellSpacing = $cellSpacing;
    }

    /**
     * @param int $cellHeight
     */
    public function setCellHeight($cellHeight)
    {
        $this->cellHeight = $cellHeight;
    }

    /**
     * @param int $articleHeight
     */
    public function setArticleHeight($articleHeight)
    {
        $this->articleHeight = $articleHeight;
    }

    /**
     * @param int $rows
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    /**
     * @param \DateTimeInterface $validFrom
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;
    }

    /**
     * @param \DateTimeInterface $validTo
     */
    public function setValidTo($validTo)
    {
        $this->validTo = $validTo;
    }

    /**
     * @param int $userId
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param bool $showListing
     */
    public function setShowListing($showListing)
    {
        $this->showListing = $showListing;
    }

    /**
     * @param bool $isLandingPage
     */
    public function setIsLandingPage($isLandingPage)
    {
        $this->isLandingPage = $isLandingPage;
    }

    /**
     * @param string $seoTitle
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @param string $seoKeywords
     */
    public function setSeoKeywords($seoKeywords)
    {
        $this->seoKeywords = $seoKeywords;
    }

    /**
     * @param string $seoDescription
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;
    }

    /**
     * @param \DateTimeInterface $createDate
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @param \DateTimeInterface $modifiedDate
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modifiedDate = $modifiedDate;
    }

    /**
     * @param int $templateId
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
    }

    /**
     * @param int[] $devices
     */
    public function setDevices($devices)
    {
        $this->devices = $devices;
    }

    /**
     * @param bool $fullscreen
     */
    public function setFullscreen($fullscreen)
    {
        $this->fullscreen = $fullscreen;
    }

    /**
     * @param string $mode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param int $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return Element[]
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * @param Element[] $elements
     */
    public function setElements(array $elements)
    {
        $this->elements = $elements;
    }

    /**
     * @return Category[]
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param Category[] $categories
     */
    public function setCategories(array $categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return EmotionTemplate|null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    public function setTemplate(EmotionTemplate $template)
    {
        $this->template = $template;
    }

    /**
     * @return \Shopware\Bundle\StoreFrontBundle\Struct\Shop[]
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param \Shopware\Bundle\StoreFrontBundle\Struct\Shop[] $shops
     */
    public function setShops(array $shops)
    {
        $this->shops = $shops;
    }

    /**
     * @return int[]
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }

    /**
     * @param int[] $categoryIds
     */
    public function setCategoryIds(array $categoryIds)
    {
        $this->categoryIds = $categoryIds;
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
     * @return bool
     */
    public function isPreview()
    {
        return $this->isPreview;
    }

    /**
     * @param bool $isPreview
     */
    public function setIsPreview($isPreview)
    {
        $this->isPreview = $isPreview;
    }

    /**
     * @return string
     */
    public function getPreviewSecret()
    {
        return $this->previewSecret;
    }

    /**
     * @param string $previewSecret
     */
    public function setPreviewSecret($previewSecret)
    {
        $this->previewSecret = $previewSecret;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize()
    {
        return get_object_vars($this);
    }
}
