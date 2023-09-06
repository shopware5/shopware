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

namespace Shopware\Bundle\EmotionBundle\Struct;

use DateTimeInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\Category;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;
use Shopware\Bundle\StoreFrontBundle\Struct\Shop;
use Shopware\Components\ObjectJsonSerializeTraitDeprecated;

class Emotion extends Extendable
{
    use ObjectJsonSerializeTraitDeprecated;

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
     * @var DateTimeInterface|null
     */
    protected $validFrom;

    /**
     * @var DateTimeInterface|null
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
     * @var DateTimeInterface|null
     */
    protected $createDate;

    /**
     * @var DateTimeInterface|null
     */
    protected $modifiedDate;

    /**
     * @var int
     */
    protected $templateId;

    /**
     * @var array<int>
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
     * @var int|null
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
     * @var array<int>
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
     * @var array<int>
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
     * @return DateTimeInterface|null
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * @return DateTimeInterface|null
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
     * @return DateTimeInterface|null
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * @return DateTimeInterface|null
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
     * @return array<int>
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
     * @return int|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int $id
     *
     * @return void
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @param bool $active
     *
     * @return void
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @param string $name
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param int $cols
     *
     * @return void
     */
    public function setCols($cols)
    {
        $this->cols = $cols;
    }

    /**
     * @param int $cellSpacing
     *
     * @return void
     */
    public function setCellSpacing($cellSpacing)
    {
        $this->cellSpacing = $cellSpacing;
    }

    /**
     * @param int $cellHeight
     *
     * @return void
     */
    public function setCellHeight($cellHeight)
    {
        $this->cellHeight = $cellHeight;
    }

    /**
     * @param int $articleHeight
     *
     * @return void
     */
    public function setArticleHeight($articleHeight)
    {
        $this->articleHeight = $articleHeight;
    }

    /**
     * @param int $rows
     *
     * @return void
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    /**
     * @param DateTimeInterface|null $validFrom
     *
     * @return void
     */
    public function setValidFrom($validFrom)
    {
        $this->validFrom = $validFrom;
    }

    /**
     * @param DateTimeInterface|null $validTo
     *
     * @return void
     */
    public function setValidTo($validTo)
    {
        $this->validTo = $validTo;
    }

    /**
     * @param int $userId
     *
     * @return void
     */
    public function setUserId($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @param bool $showListing
     *
     * @return void
     */
    public function setShowListing($showListing)
    {
        $this->showListing = $showListing;
    }

    /**
     * @param bool $isLandingPage
     *
     * @return void
     */
    public function setIsLandingPage($isLandingPage)
    {
        $this->isLandingPage = $isLandingPage;
    }

    /**
     * @param string $seoTitle
     *
     * @return void
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @param string $seoKeywords
     *
     * @return void
     */
    public function setSeoKeywords($seoKeywords)
    {
        $this->seoKeywords = $seoKeywords;
    }

    /**
     * @param string $seoDescription
     *
     * @return void
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;
    }

    /**
     * @param DateTimeInterface|null $createDate
     *
     * @return void
     */
    public function setCreateDate($createDate)
    {
        $this->createDate = $createDate;
    }

    /**
     * @param DateTimeInterface|null $modifiedDate
     *
     * @return void
     */
    public function setModifiedDate($modifiedDate)
    {
        $this->modifiedDate = $modifiedDate;
    }

    /**
     * @param int $templateId
     *
     * @return void
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
    }

    /**
     * @param array<int> $devices
     *
     * @return void
     */
    public function setDevices($devices)
    {
        $this->devices = $devices;
    }

    /**
     * @param bool $fullscreen
     *
     * @return void
     */
    public function setFullscreen($fullscreen)
    {
        $this->fullscreen = $fullscreen;
    }

    /**
     * @param string $mode
     *
     * @return void
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /**
     * @param int $position
     *
     * @return void
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @param int|null $parentId
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
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

    /**
     * @return void
     */
    public function setTemplate(EmotionTemplate $template)
    {
        $this->template = $template;
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
     *
     * @return void
     */
    public function setShops(array $shops)
    {
        $this->shops = $shops;
    }

    /**
     * @return array<int>
     */
    public function getCategoryIds()
    {
        return $this->categoryIds;
    }

    /**
     * @param array<int> $categoryIds
     *
     * @return void
     */
    public function setCategoryIds(array $categoryIds)
    {
        $this->categoryIds = $categoryIds;
    }

    /**
     * @return array<int>
     */
    public function getShopIds()
    {
        return $this->shopIds;
    }

    /**
     * @param array<int> $shopIds
     *
     * @return void
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
     *
     * @return void
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
     *
     * @return void
     */
    public function setPreviewSecret($previewSecret)
    {
        $this->previewSecret = $previewSecret;
    }
}
