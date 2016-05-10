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

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Models\Emotion\Template;

/**
 * The Shopware Emotion Model enables you to adapt the view of a category individually according to a grid system.
 * Every emotion is assigned to a certain grid which consists of several rows and columns.
 * The width and height of the cells resulting from the columns and rows can be configured individually.
 * Again, a grid which is assigned to an emotion is assigned to multiple other grid components.
 * A grid element may extend over several cells. The grid elements can be filled with components
 * from the component library, such as banners, items or text elements.
 *
 * @category   Shopware
 * @package    Shopware\Models
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_emotion")
 * @ORM\HasLifecycleCallbacks
 */
class Emotion extends ModelEntity
{
    /**
     * Unique identifier field for the shopware emotion.
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var integer $id
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId = null;

    /**
     * Is this emotion active
     *
     * @var integer $active
     *
     * @ORM\Column(name="active", type="integer", nullable=false)
     */
    private $active;

    /**
     * Contains the name of the emotion.
     *
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Id of the associated \Shopware\Models\User\User which
     * created this emotion.
     *
     * @var integer $userID
     *
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    private $userId;

    /**
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position = 1;

    /**
     * @var integer $device
     *
     * @ORM\Column(name="device", type="string", length=255, nullable=true)
     */
    private $device;

    /**
     * @var integer $fullscreen
     *
     * @ORM\Column(name="fullscreen", type="integer", nullable=false)
     */
    private $fullscreen;

    /**
     * With the $validFrom and $validTo property you can define
     * a date range in which the emotion will be displayed.
     *
     * @var \DateTime $validFrom
     *
     * @ORM\Column(name="valid_from", type="datetime", nullable=true)
     */
    private $validFrom;

    /**
     * @var integer $isLandingPage
     *
     * @ORM\Column(name="is_landingpage", type="integer", nullable=false)
     */
    private $isLandingPage;

    /**
     * @var string $seoTitle
     *
     * @ORM\Column(name="seo_title", type="string",length=255, nullable=false)
     */
    private $seoTitle;

    /**
     * @var string $seoKeywords
     *
     * @ORM\Column(name="seo_keywords", type="string",length=255, nullable=false)
     */
    private $seoKeywords;

    /**
     * @var string $seoDescription
     *
     * @ORM\Column(name="seo_description", type="string",length=255, nullable=false)
     */
    private $seoDescription;

    /**
     * With the $validFrom and $validTo property you can define
     * a date range in which the emotion will be displayed.
     *
     * @var \DateTime $validTo
     *
     * @ORM\Column(name="valid_to", type="datetime", nullable=true)
     */
    private $validTo;

    /**
     * Create date of the emotion.
     *
     * @var \DateTime $createDate
     *
     * @ORM\Column(name="create_date", type="datetime", nullable=false)
     */
    private $createDate;

    /**
     * Date of the last edit.
     *
     * @var \DateTime $modified
     *
     * @ORM\Column(name="modified", type="datetime", nullable=false)
     */
    private $modified;

    /**
     * @var int
     * @ORM\Column(name="rows", type="integer", nullable=false)
     */
    private $rows;

    /**
     * @var int
     * @ORM\Column(name="cols", type="integer", nullable=false)
     */
    private $cols;

    /**
     * @var int
     * @ORM\Column(name="cell_spacing", type="integer", nullable=false)
     */
    private $cellSpacing;

    /**
     * @var int
     * @ORM\Column(name="cell_height", type="integer", nullable=false)
     */
    private $cellHeight;

    /**
     * @var int
     * @ORM\Column(name="article_height", type="integer", nullable=false)
     */
    private $articleHeight;

    /**
     * Contains the assigned \Shopware\Models\Category\Category
     * which can be configured in the backend emotion module.
     * The assigned grid will be displayed in front of the categories.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Category\Category", inversedBy="emotions")
     * @ORM\JoinTable(name="s_emotion_categories",
     *      joinColumns={
     *          @ORM\JoinColumn(name="emotion_id", referencedColumnName="id")
     *      },
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     *      }
     * )
     */
    protected $categories;

    /**
     * Contains the assigned Shopware\Models\Shop\Shop.
     * Used for shop limitation of single emotion landingpages.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinTable(name="s_emotion_shops",
     *      joinColumns={
     *          @ORM\JoinColumn(name="emotion_id", referencedColumnName="id"
     *      )},
     *      inverseJoinColumns={
     *          @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     *      }
     * )
     */
    private $shops;

    /**
     * OWNING SIDE
     * Contains the assigned \Shopware\Models\User\User which created this emotion.
     *
     * @var \Shopware\Models\User\User $user
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
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\Element", mappedBy="emotion", orphanRemoval=true, cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $elements;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Emotion", mappedBy="emotion", orphanRemoval=true, cascade={"persist"})
     * @var \Shopware\Models\Attribute\Emotion
     */
    protected $attribute;

    /**
     * @var boolean $isLandingPage
     * @ORM\Column(name="show_listing", type="boolean", nullable=false)
     */
    protected $showListing;

    /**
     * @var
     * @ORM\Column(name="template_id", type="integer", nullable=true)
     */
    protected $templateId = null;

    /**
     * @var Template
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Emotion\Template", inversedBy="emotions")
     * @ORM\JoinColumn(name="template_id", referencedColumnName="id")
     */
    protected $template;

    /**
     * Contains the responsive mode of the emotion.
     *
     * @var string $mode
     *
     * @ORM\Column(name="mode", type="string", length=255, nullable=false)
     */
    private $mode;

    /**
     * Class constructor.
     */
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

    /**
     * Unique identifier field for the shopware emotion.
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Contains the name of the emotion.
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * Contains the name of the emotion.
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Create date of the emotion.
     *
     * @param \DateTime|string $createDate
     */
    public function setCreateDate($createDate = 'now')
    {
        if ($createDate !== null && !($createDate instanceof \DateTime)) {
            $this->createDate = new \DateTime($createDate);
        } else {
            $this->createDate = $createDate;
        }
    }

    /**
     * Create date of the emotion.
     *
     * @return \DateTime
     */
    public function getCreateDate()
    {
        return $this->createDate;
    }

    /**
     * With the $validFrom and $validTo property you can define
     * a date range in which the emotion will be displayed.
     *
     * @param \DateTime|string $validFrom
     */
    public function setValidFrom($validFrom)
    {
        if ($validFrom !== null && !($validFrom instanceof \DateTime)) {
            $this->validFrom = new \DateTime($validFrom);
        } else {
            $this->validFrom = $validFrom;
        }
    }

    /**
     * With the $validFrom and $validTo property you can define
     * a date range in which the emotion will be displayed.
     *
     * @return \DateTime
     */
    public function getValidFrom()
    {
        return $this->validFrom;
    }

    /**
     * With the $validFrom and $validTo property you can define
     * a date range in which the emotion will be displayed.
     *
     * @param \DateTime|string $validTo
     */
    public function setValidTo($validTo)
    {
        if ($validTo !== null && !($validTo instanceof \DateTime)) {
            $this->validTo = new \DateTime($validTo);
        } else {
            $this->validTo = $validTo;
        }
    }

    /**
     * With the $validFrom and $validTo property you can define
     * a date range in which the emotion will be displayed.
     *
     * @return \DateTime
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
     * @return \DateTime
     */
    public function getModified()
    {
        return $this->modified;
    }

    /**
     * @param \DateTime|string $modified
     */
    public function setModified($modified)
    {
        if ($modified !== null && !($modified instanceof \DateTime)) {
            $this->modified = new \DateTime($modified);
        } else {
            $this->modified = $modified;
        }
    }

    /**
     * @return \Shopware\Models\Attribute\Emotion
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\Emotion|array|null $attribute
     * @return \Shopware\Models\Attribute\Emotion
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Emotion', 'attribute', 'emotion');
    }

    /**
     * Contains all the assigned \Shopware\Models\Emotion\Element models.
     * The element model contains the configuration about the size and position of the element
     * and the assigned \Shopware\Models\Emotion\Library\Component which contains the data configuration.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
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
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $elements
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function setElements($elements)
    {
        return $this->setOneToMany($elements, '\Shopware\Models\Emotion\Element', 'elements', 'emotion');
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
     * @return boolean
     */
    public function getShowListing()
    {
        return $this->showListing;
    }

    /**
     * @param boolean $showListing
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
     * @param $rows
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

    public function __clone()
    {
        $this->id = null;

        $categories = array();
        foreach ($this->getCategories() as $category) {
            $categories[] = $category;
        }

        $elements = array();
        /**@var $element Element*/
        foreach ($this->getElements() as $element) {
            $newElement = clone $element;
            $newElement->setEmotion($this);

            if ($newElement->getData()) {
                /**@var $data Data*/
                foreach ($newElement->getData() as $data) {
                    $data->setEmotion($this);
                }
            }

            $elements[] = $newElement;
        }

        if ($attribute = $this->getAttribute()) {
            /** @var Shopware\Models\Attribute\Emotion $newAttribute */
            $newAttribute = clone $attribute;
            $newAttribute->setEmotion($this);
            $this->attribute = $newAttribute;
        }

        $this->elements = $elements;
        $this->categories = $categories;
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
}
