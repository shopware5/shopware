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

namespace Shopware\Models\Emotion;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\Emotion as EmotionAttribute;
use Shopware\Models\Category\Category;
use Shopware\Models\Shop\Shop;
use Shopware\Models\User\User;

/**
 * The Shopware Emotion Model enables you to adapt the view of a category individually according to a grid system.
 * Every emotion is assigned to a certain grid which consists of several rows and columns.
 * The width and height of the cells resulting from the columns and rows can be configured individually.
 * Again, a grid which is assigned to an emotion is assigned to multiple other grid components.
 * A grid element may extend over several cells. The grid elements can be filled with components
 * from the component library, such as banners, items or text elements.
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_emotion")
 * @ORM\HasLifecycleCallbacks()
 */
class Emotion extends ModelEntity
{
    public const LISTING_VISIBILITY_ONLY_START = 'only_start';
    public const LISTING_VISIBILITY_ONLY_START_AND_LISTING = 'start_and_listing';
    public const LISTING_VISIBILITY_ONLY_LISTING = 'only_listing';

    /**
     * Contains the assigned \Shopware\Models\Category\Category
     * which can be configured in the backend emotion module.
     * The assigned grid will be displayed in front of the categories.
     *
     * @var ArrayCollection<Category>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Category\Category", inversedBy="emotions")
     * @ORM\JoinTable(name="s_emotion_categories",
     *     joinColumns={
     *         @ORM\JoinColumn(name="emotion_id", referencedColumnName="id", nullable=false)
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="category_id", referencedColumnName="id", nullable=false)
     *     }
     * )
     */
    protected $categories;

    /**
     * OWNING SIDE
     * Contains the assigned \Shopware\Models\User\User which created this emotion.
     *
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\User\User")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id", nullable=true)
     */
    protected $user;

    /**
     * INVERSE SIDE
     * Contains all the assigned \Shopware\Models\Emotion\Element models.
     * The element model contains the configuration about the size and position of the element
     * and the assigned \Shopware\Models\Emotion\Library\Component which contains the data configuration.
     *
     * @var ArrayCollection<Element>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\Element", mappedBy="emotion", orphanRemoval=true, cascade={"persist"})
     */
    protected $elements;

    /**
     * INVERSE SIDE
     *
     * @var EmotionAttribute|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Emotion", mappedBy="emotion", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var bool
     *
     * @ORM\Column(name="show_listing", type="boolean", nullable=false)
     */
    protected $showListing;

    /**
     * @var int|null
     *
     * @ORM\Column(name="template_id", type="integer", nullable=true)
     */
    protected $templateId;

    /**
     * @var Template|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Emotion\Template", inversedBy="emotions")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * Unique identifier field for the shopware emotion.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int|null
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * Is this emotion active
     *
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * Contains the name of the emotion.
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * ID of the associated \Shopware\Models\User\User which created this emotion.
     *
     * @var int|null
     *
     * @ORM\Column(name="userID", type="integer", nullable=true)
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 1;

    /**
     * @var string|null
     *
     * @ORM\Column(name="device", type="string", length=255, nullable=true)
     */
    private $device = '0,1,2,3,4';

    /**
     * @var bool
     *
     * @ORM\Column(name="fullscreen", type="boolean", nullable=false)
     */
    private $fullscreen;

    /**
     * With the $validFrom and $validTo property you can define
     * a date range in which the emotion will be displayed.
     *
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=true)
     */
    private $validFrom;

    /**
     * @var bool
     *
     * @ORM\Column(name="is_landingpage", type="boolean", nullable=false)
     */
    private $isLandingPage;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_title", type="string", length=255, nullable=false)
     */
    private $seoTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_keywords", type="string", length=255, nullable=false)
     */
    private $seoKeywords;

    /**
     * @var string
     *
     * @ORM\Column(name="seo_description", type="string", length=255, nullable=false)
     */
    private $seoDescription;

    /**
     * With the $validFrom and $validTo property you can define
     * a date range in which the emotion will be displayed.
     *
     * @var \DateTimeInterface|null
     *
     * @ORM\Column(name="valid_to", type="datetime", nullable=true)
     */
    private $validTo;

    /**
     * Create date of the emotion.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="create_date", type="datetime", nullable=false)
     */
    private $createDate;

    /**
     * Date of the last edit.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="modified", type="datetime", nullable=false)
     */
    private $modified;

    /**
     * @var int
     *
     * @ORM\Column(name="`rows`", type="integer", nullable=false)
     */
    private $rows;

    /**
     * @var int
     *
     * @ORM\Column(name="cols", type="integer", nullable=false)
     */
    private $cols;

    /**
     * @var int
     *
     * @ORM\Column(name="cell_spacing", type="integer", nullable=false)
     */
    private $cellSpacing;

    /**
     * @var int
     *
     * @ORM\Column(name="cell_height", type="integer", nullable=false)
     */
    private $cellHeight;

    /**
     * @var int
     *
     * @ORM\Column(name="article_height", type="integer", nullable=false)
     */
    private $articleHeight;

    /**
     * Contains the assigned Shopware\Models\Shop\Shop.
     * Used for shop limitation of single emotion landingpages.
     *
     * @var ArrayCollection<Shop>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinTable(name="s_emotion_shops",
     *     joinColumns={
     *         @ORM\JoinColumn(name="emotion_id", referencedColumnName="id"
     *         )},
     *         inverseJoinColumns={
     *             @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     *         }
     *     )
     */
    private $shops;

    /**
     * Contains the responsive mode of the emotion.
     *
     * @var string
     *
     * @ORM\Column(name="mode", type="string", length=255, nullable=false)
     */
    private $mode;

    /**
     * @var int|null
     *
     * @ORM\Column(name="preview_id", type="integer", nullable=true)
     */
    private $previewId;

    /**
     * @var string|null
     *
     * @ORM\Column(name="preview_secret", type="string", nullable=true)
     */
    private $previewSecret;

    /**
     * only_start => displayed only on category page
     * start_and_listing => display in listing (?p=1) and category page
     * only_listing => only displayed in category listing page
     *
     * @var string
     * @ORM\Column(name="listing_visibility", type="string", nullable=false)
     */
    private $listingVisibility = self::LISTING_VISIBILITY_ONLY_START;

    /**
     * @var string|null
     *
     * @ORM\Column(name="customer_stream_ids", type="string", nullable=true)
     */
    private $customerStreamIds;

    /**
     * @var string|null
     *
     * @ORM\Column(name="replacement", type="string", nullable=true)
     */
    private $replacement;

    public function __construct()
    {
        $this->shops = new ArrayCollection();
        $this->categories = new ArrayCollection();
        $this->elements = new ArrayCollection();

        $this->setRows(20);
        $this->setCols(4);
        $this->setCellSpacing(10);
        $this->setCellHeight(185);
        $this->setArticleHeight(2);
    }

    public function __clone()
    {
        $this->id = null;

        $categories = [];
        foreach ($this->getCategories() as $category) {
            $categories[] = $category;
        }

        $elements = [];
        /** @var Element $element */
        foreach ($this->getElements() as $element) {
            $newElement = clone $element;
            $newElement->setEmotion($this);

            if ($newElement->getData()) {
                /** @var Data $data */
                foreach ($newElement->getData() as $data) {
                    $data->setEmotion($this);
                }
            }

            $elements[] = $newElement;
        }

        if ($attribute = $this->getAttribute()) {
            /** @var EmotionAttribute $newAttribute */
            $newAttribute = clone $attribute;
            $newAttribute->setEmotion($this);
            $this->attribute = $newAttribute;
        }

        $this->elements = new ArrayCollection($elements);
        $this->categories = new ArrayCollection($categories);
    }

    /**
     * Unique identifier field for the shopware emotion.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Contains the name of the emotion.
     *
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Contains the name of the emotion.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Create date of the emotion.
     *
     * @param \DateTimeInterface|string $createDate
     */
    public function setCreateDate($createDate = 'now')
    {
        if ($createDate !== null && !($createDate instanceof \DateTimeInterface)) {
            $this->createDate = new \DateTime($createDate);
        } else {
            $this->createDate = $createDate;
        }
    }

    /**
     * Create date of the emotion.
     *
     * @return \DateTimeInterface
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * With the $validFrom and $validTo property you can define
     * a date range in which the emotion will be displayed.
     *
     * @param \DateTimeInterface|string|null $validFrom
     */
    public function setValidFrom($validFrom)
    {
        if ($validFrom !== null && !($validFrom instanceof \DateTimeInterface)) {
            $this->validFrom = new \DateTime($validFrom);
        } else {
            $this->validFrom = $validFrom;
        }
    }

    /**
     * With the $validFrom and $validTo property you can define
     * a date range in which the emotion will be displayed.
     *
     * @return \DateTimeInterface|null
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * With the $validFrom and $validTo property you can define
     * a date range in which the emotion will be displayed.
     *
     * @param \DateTimeInterface|string|null $validTo
     */
    public function setValidTo($validTo)
    {
        if ($validTo !== null && !($validTo instanceof \DateTimeInterface)) {
            $this->validTo = new \DateTime($validTo);
        } else {
            $this->validTo = $validTo;
        }
    }

    /**
     * With the $validFrom and $validTo property you can define
     * a date range in which the emotion will be displayed.
     *
     * @return \DateTimeInterface|null
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * Contains the assigned \Shopware\Models\User\User which
     * created this emotion.
     *
     * @param User|null $user
     *
     * @return Emotion
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Contains the assigned \Shopware\Models\User\User which
     * created this emotion.
     *
     * @return User|null
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param \DateTimeInterface|string $modified
     */
    public function setModified($modified)
    {
        if ($modified !== null && !($modified instanceof \DateTimeInterface)) {
            $this->modified = new \DateTime($modified);
        } else {
            $this->modified = $modified;
        }
    }

    /**
     * @return EmotionAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param EmotionAttribute|array|null $attribute
     *
     * @return Emotion
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, EmotionAttribute::class, 'attribute', 'emotion');
    }

    /**
     * Contains all the assigned \Shopware\Models\Emotion\Element models.
     * The element model contains the configuration about the size and position of the element
     * and the assigned \Shopware\Models\Emotion\Library\Component which contains the data configuration.
     *
     * @return ArrayCollection<Element>
     */
    public function getElements()
    {
        return $this->elements;
    }

    /**
     * INVERSE SIDE
     * Contains all the assigned \Shopware\Models\Emotion\Element models.
     * The element model contains the configuration about the size and position of the element
     * and the assigned \Shopware\Models\Emotion\Library\Component which contains the data configuration.
     *
     * @param Element[]|null $elements
     *
     * @return Emotion
     */
    public function setElements($elements)
    {
        return $this->setOneToMany($elements, Element::class, 'elements', 'emotion');
    }

    /**
     * @param bool $isLandingPage
     */
    public function setIsLandingPage($isLandingPage)
    {
        $this->isLandingPage = $isLandingPage;
    }

    /**
     * @return bool
     */
    public function getIsLandingPage()
    {
        return $this->isLandingPage;
    }

    /**
     * @param string $seoDescription
     */
    public function setSeoDescription($seoDescription)
    {
        $this->seoDescription = $seoDescription;
    }

    /**
     * @return string
     */
    public function getSeoDescription()
    {
        return $this->seoDescription;
    }

    /**
     * @param string $seoTitle
     */
    public function setSeoTitle($seoTitle)
    {
        $this->seoTitle = $seoTitle;
    }

    /**
     * @return string
     */
    public function getSeoTitle()
    {
        return $this->seoTitle;
    }

    /**
     * @param string $seoKeywords
     */
    public function setSeoKeywords($seoKeywords)
    {
        $this->seoKeywords = $seoKeywords;
    }

    /**
     * @return string
     */
    public function getSeoKeywords()
    {
        return $this->seoKeywords;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @return ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param ArrayCollection $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return ArrayCollection
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param ArrayCollection $shops
     */
    public function setShops($shops)
    {
        $this->shops = $shops;
    }

    /**
     * @return bool
     */
    public function getShowListing()
    {
        return $this->showListing;
    }

    /**
     * @param bool $showListing
     */
    public function setShowListing($showListing)
    {
        $this->showListing = $showListing;
    }

    /**
     * @return Template|null
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param Template $template
     */
    public function setTemplate(Template $template = null)
    {
        $this->template = $template;
    }

    /**
     * @param string $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }

    /**
     * @return string|null
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param bool $fullscreen
     */
    public function setFullscreen($fullscreen)
    {
        $this->fullscreen = $fullscreen;
    }

    /**
     * @return bool
     */
    public function getFullscreen()
    {
        return $this->fullscreen;
    }

    /**
     * @param int $rows
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    /**
     * @return int
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * @return int
     */
    public function getCols()
    {
        return $this->cols;
    }

    /**
     * @param int $cols
     */
    public function setCols($cols)
    {
        $this->cols = $cols;
    }

    /**
     * @return int
     */
    public function getCellSpacing()
    {
        return $this->cellSpacing;
    }

    /**
     * @param int $cellSpacing
     */
    public function setCellSpacing($cellSpacing)
    {
        $this->cellSpacing = $cellSpacing;
    }

    /**
     * @return int
     */
    public function getCellHeight()
    {
        return $this->cellHeight;
    }

    /**
     * @param int $cellHeight
     */
    public function setCellHeight($cellHeight)
    {
        $this->cellHeight = $cellHeight;
    }

    /**
     * @return int
     */
    public function getArticleHeight()
    {
        return $this->articleHeight;
    }

    /**
     * @param int $articleHeight
     */
    public function setArticleHeight($articleHeight)
    {
        $this->articleHeight = $articleHeight;
    }

    /*
     * @param string $responsiveMode
     */
    public function setMode($mode)
    {
        $this->mode = $mode;
    }

    /*
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
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int|null
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * @param int|null $parentId
     */
    public function setParentId($parentId)
    {
        $this->parentId = $parentId;
    }

    /**
     * @return int|null
     */
    public function getPreviewId()
    {
        return $this->previewId;
    }

    /**
     * @param int|null $previewId
     */
    public function setPreviewId($previewId)
    {
        $this->previewId = $previewId;
    }

    /**
     * @return string|null
     */
    public function getPreviewSecret()
    {
        return $this->previewSecret;
    }

    /**
     * @param string|null $previewSecret
     */
    public function setPreviewSecret($previewSecret)
    {
        $this->previewSecret = $previewSecret;
    }

    /**
     * @return string
     */
    public function getListingVisibility()
    {
        return $this->listingVisibility;
    }

    /**
     * @param string $listingVisibility
     */
    public function setListingVisibility($listingVisibility)
    {
        $this->listingVisibility = $listingVisibility;
    }

    /**
     * @return string|null
     */
    public function getReplacement()
    {
        return $this->replacement;
    }

    /**
     * @param string|null $replacement
     */
    public function setReplacement($replacement)
    {
        $this->replacement = $replacement;
    }

    /**
     * @return string|null
     */
    public function getCustomerStreamIds()
    {
        return $this->customerStreamIds;
    }

    /**
     * @param string|null $customerStreamIds
     */
    public function setCustomerStreamIds($customerStreamIds)
    {
        $this->customerStreamIds = $customerStreamIds;
    }
}
