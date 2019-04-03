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

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * The Shopware Emotion Model enables you to adapt the view of a category individually according to a grid system.
 * Every emotion is assigned to a certain grid which consists of several rows and columns.
 * The width and height of the cells resulting from the columns and rows can be configured individually.
 * Again, a grid which is assigned to an emotion is assigned to multiple other grid components.
 * A grid element may extend over several cells. The grid elements can be filled with components
 * from the component library, such as banners, items or text elements.
 *
 *
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_emotion")
 * @ORM\HasLifecycleCallbacks()
 */
class Emotion extends ModelEntity
{
    const LISTING_VISIBILITY_ONLY_START = 'only_start';
    const LISTING_VISIBILITY_ONLY_START_AND_LISTING = 'start_and_listing';
    const LISTING_VISIBILITY_ONLY_LISTING = 'only_listing';

    /**
     * Contains the assigned \Shopware\Models\Category\Category
     * which can be configured in the backend emotion module.
     * The assigned grid will be displayed in front of the categories.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Category\Category>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Category\Category", inversedBy="emotions")
     * @ORM\JoinTable(name="s_emotion_categories",
     *     joinColumns={
     *         @ORM\JoinColumn(name="emotion_id", referencedColumnName="id")
     *     },
     *     inverseJoinColumns={
     *         @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *     }
     * )
     */
    protected $categories;

    /**
     * OWNING SIDE
     * Contains the assigned \Shopware\Models\User\User which created this emotion.
     *
     * @var \Shopware\Models\User\User
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\User\User")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    protected $user;

    /**
     * INVERSE SIDE
     * Contains all the assigned \Shopware\Models\Emotion\Element models.
     * The element model contains the configuration about the size and position of the element
     * and the assigned \Shopware\Models\Emotion\Library\Component which contains the data configuration.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Emotion\Element>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\Element", mappedBy="emotion", orphanRemoval=true, cascade={"persist"})
     */
    protected $elements;

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\Attribute\Emotion
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
     * @var int
     *
     * @ORM\Column(name="template_id", type="integer", nullable=true)
     */
    protected $templateId = null;

    /**
     * @var Template
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
     * @var int
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId = null;

    /**
     * Is this emotion active
     *
     * @var int
     *
     * @ORM\Column(name="active", type="integer", nullable=false)
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
     * Id of the associated \Shopware\Models\User\User which
     * created this emotion.
     *
     * @var int
     *
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 1;

    /**
     * @var int
     *
     * @ORM\Column(name="device", type="string", length=255, nullable=true)
     */
    private $device;

    /**
     * @var int
     *
     * @ORM\Column(name="fullscreen", type="integer", nullable=false)
     */
    private $fullscreen;

    /**
     * With the $validFrom and $validTo property you can define
     * a date range in which the emotion will be displayed.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=true)
     */
    private $validFrom;

    /**
     * @var int
     *
     * @ORM\Column(name="is_landingpage", type="integer", nullable=false)
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
     * @var \DateTimeInterface
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
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Shop\Shop>
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
     * @var int
     *
     * @ORM\Column(name="preview_id", type="integer", nullable=true)
     */
    private $previewId;

    /**
     * @var string
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
     * @var string
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
        $this->shops = new \Doctrine\Common\Collections\ArrayCollection();
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection();
        $this->elements = new \Doctrine\Common\Collections\ArrayCollection();

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
            /** @var \Shopware\Models\Attribute\Emotion $newAttribute */
            $newAttribute = clone $attribute;
            $newAttribute->setEmotion($this);
            $this->attribute = $newAttribute;
        }

        $this->elements = new \Doctrine\Common\Collections\ArrayCollection($elements);
        $this->categories = new \Doctrine\Common\Collections\ArrayCollection($categories);
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
     * @param \DateTimeInterface|string|null $createDate
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
     * @return \DateTimeInterface
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
     * @return \DateTimeInterface
     */
    public function getValidTo()
    {
        return $this->validTo;
    }

    /**
     * Contains the assigned \Shopware\Models\User\User which
     * created this emotion.
     *
     * @param \Shopware\Models\User\User $user
     *
     * @return \Shopware\Models\Emotion\Emotion
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
     * @return \Shopware\Models\User\User
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
     * @param \DateTimeInterface|string|null $modified
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
     * @return \Shopware\Models\Attribute\Emotion|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\Emotion|array|null $attribute
     *
     * @return Emotion
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, \Shopware\Models\Attribute\Emotion::class, 'attribute', 'emotion');
    }

    /**
     * Contains all the assigned \Shopware\Models\Emotion\Element models.
     * The element model contains the configuration about the size and position of the element
     * and the assigned \Shopware\Models\Emotion\Library\Component which contains the data configuration.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Emotion\Element>
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
     * @param \Shopware\Models\Emotion\Element[]|null $elements
     *
     * @return Emotion
     */
    public function setElements($elements)
    {
        return $this->setOneToMany($elements, \Shopware\Models\Emotion\Element::class, 'elements', 'emotion');
    }

    /**
     * @param int $isLandingPage
     */
    public function setIsLandingPage($isLandingPage)
    {
        $this->isLandingPage = $isLandingPage;
    }

    /**
     * @return int
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
     * @param int $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getCategories()
    {
        return $this->categories;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $categories
     */
    public function setCategories($categories)
    {
        $this->categories = $categories;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getShops()
    {
        return $this->shops;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $shops
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
     * @return Template
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
     * @param int $device
     */
    public function setDevice($device)
    {
        $this->device = $device;
    }

    /**
     * @return int
     */
    public function getDevice()
    {
        return $this->device;
    }

    /**
     * @param int $fullscreen
     */
    public function setFullscreen($fullscreen)
    {
        $this->fullscreen = $fullscreen;
    }

    /**
     * @return int
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
    public function getPreviewId()
    {
        return $this->previewId;
    }

    /**
     * @param int $previewId
     */
    public function setPreviewId($previewId)
    {
        $this->previewId = $previewId;
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

    public function getCustomerStreamIds()
    {
        return $this->customerStreamIds;
    }

    public function setCustomerStreamIds($customerStreamIds)
    {
        $this->customerStreamIds = $customerStreamIds;
    }
}
