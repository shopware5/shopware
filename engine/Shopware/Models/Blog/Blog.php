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

namespace Shopware\Models\Blog;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Shopware Blog Model
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_blog")
 */
class Blog extends ModelEntity
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
     * @var string $title
     *
     * @ORM\Column(name="title", type="string", nullable=false)
     */
    private $title;

    /**
     * @var integer $authorId
     *
     * @ORM\Column(name="author_id", type="integer", nullable=true)
     */
    private $authorId = null;

    /**
     * Flag which shows if the blog is active or not. 1= active otherwise inactive
     *
     * @var boolean $active
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var string $shortDescription
     *
     * @ORM\Column(name="short_description", type="string", nullable=false)
     */
    private $shortDescription;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var integer $views
     *
     * @ORM\Column(name="views", type="integer", nullable=true)
     */
    private $views = null;

    /**
     * @var \DateTime $displayDate
     *
     * @ORM\Column(name="display_date", type="datetime", nullable=false)
     */
    private $displayDate;

    /**
     * @var integer $categoryId
     *
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     */
    private $categoryId = null;

    /**
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Category\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @var string $template
     *
     * @ORM\Column(name="template", type="string", nullable=false)
     */
    private $template;

    /**
     * @var string $metaKeyWords
     *
     * @ORM\Column(name="meta_keywords", type="string", nullable=true)
     */
    private $metaKeyWords;

    /**
     * @var string $metaDescription
     *
     * @ORM\Column(name="meta_description", type="string", nullable=true)
     */
    private $metaDescription;

    /**
     * @var string $metaTitle
     *
     * @ORM\Column(name="meta_title", type="string", nullable=true)
     */
    private $metaTitle;

    /**
     * INVERSE SIDE
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Blog\Tag", mappedBy="blog", orphanRemoval=true)
     * @var ArrayCollection
     */
    protected $tags;

    /**
     * INVERSE SIDE
     * @ORM\OneToMany(targetEntity="Shopware\Models\Blog\Media", mappedBy="blog", orphanRemoval=true, cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $media;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinTable(name="s_blog_assigned_articles",
     *      joinColumns={@ORM\JoinColumn(name="blog_id", referencedColumnName="id")},
     *      inverseJoinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id")}
     *      )
     */
    protected $assignedArticles;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Blog", mappedBy="blog", cascade={"persist"})
     * @var \Shopware\Models\Attribute\Blog
     */
    protected $attribute;

    /**
     * INVERSE SIDE
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Blog\Comment", mappedBy="blog", orphanRemoval=true, cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection An array of \Shopware\Models\Blog\Comment Objects
     */
    protected $comments;

    /**
     * INVERSE SIDE
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\User\User", inversedBy="blog")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
     */
    private $author;


    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->media = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->assignedArticles = new ArrayCollection();
    }

    /**
     * Get Id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get Title
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set Title
     *
     * @param string $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return \Shopware\Models\Category\Category
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param \Shopware\Models\Category\Category $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
    }

    /**
     * Get Active
     *
     * @return boolean
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * Set Active
     *
     * @param boolean $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * Get ShortDescription
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * Set ShortDescription
     *
     * @param string $shortDescription
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
    }

    /**
     * Get Description
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set Description
     *
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * Get Views
     *
     * @return int
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * Set Views
     *
     * @param int $views
     */
    public function setViews($views)
    {
        $this->views = $views;
    }

    /**
     * Get DisplayDate
     *
     * @return \DateTime
     */
    public function getDisplayDate()
    {
        return $this->displayDate;
    }

    /**
     * Set DisplayDate
     *
     * @param \DateTime|string $displayDate
     */
    public function setDisplayDate($displayDate)
    {
        if (!$displayDate instanceof \DateTime && strlen($displayDate) > 0) {
            $displayDate = new \DateTime($displayDate);
        }
        $this->displayDate = $displayDate;
    }

    /**
     * Get CategoryId
     *
     * @return int
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * Set CategoryId
     *
     * @param int $categoryId
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    /**
     * Get Tags
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Set Tags
     *
     * @param ArrayCollection
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * Get Template
     *
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * Set Template
     *
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }


    /**
     * Get Media
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * Set Media
     *
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $media
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function setMedia($media)
    {
        return $this->setOneToMany($media, '\Shopware\Models\Blog\Media', 'media', 'blog');
    }

    /**
     * Get the assigned articles
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getAssignedArticles()
    {
        return $this->assignedArticles;
    }

    /**
     * Set the assigned articles
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $assignedArticles
     */
    public function setAssignedArticles($assignedArticles)
    {
        $this->assignedArticles = $assignedArticles;
    }

    /**
     * Set the metaKeyWords
     * @return string
     */
    public function getMetaKeyWords()
    {
        return $this->metaKeyWords;
    }

    /**
     * Get the metaKeyWords
     *
     * @param string $metaKeyWords
     */
    public function setMetaKeyWords($metaKeyWords)
    {
        $this->metaKeyWords = $metaKeyWords;
    }

    /**
     * Get the metaDescription
     *
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * Set the metaDescription
     *
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * Returns the Attributes
     *
     * @return \Shopware\Models\Attribute\Blog
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Returns the blog attribute
     *
     * @param \Shopware\Models\Attribute\Blog|array|null $attribute
     * @return \Shopware\Models\Attribute\Blog
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Blog', 'attribute', 'blog');
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $comments
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function setComments($comments)
    {
        return $this->setOneToMany($comments, '\Shopware\Models\Blog\Comment', 'comments', 'blog');
    }

    /**
     * returns the author
     *
     * @return mixed
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * sets the author
     *
     * @param $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * set the metaTitle
     *
     * @param string $metaTitle
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * returns the metaTitle
     *
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }
}
