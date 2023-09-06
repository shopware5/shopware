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

namespace Shopware\Models\Blog;

use DateTime;
use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Article\Article;
use Shopware\Models\Attribute\Blog as BlogAttribute;
use Shopware\Models\Category\Category;
use Shopware\Models\User\User;

/**
 * Shopware Blog Model
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_blog")
 */
class Blog extends ModelEntity
{
    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<Tag>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Blog\Tag", mappedBy="blog", orphanRemoval=true)
     */
    protected $tags;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<Media>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Blog\Media", mappedBy="blog", orphanRemoval=true, cascade={"persist"})
     */
    protected $media;

    /**
     * @var ArrayCollection<Article>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinTable(name="s_blog_assigned_articles",
     *     joinColumns={@ORM\JoinColumn(name="blog_id", referencedColumnName="id", nullable=false)},
     *     inverseJoinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id", nullable=false)}
     * )
     */
    protected $assignedArticles;

    /**
     * INVERSE SIDE
     *
     * @var BlogAttribute|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Blog", mappedBy="blog", cascade={"persist"})
     */
    protected $attribute;

    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<Comment>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Blog\Comment", mappedBy="blog", orphanRemoval=true, cascade={"persist"})
     */
    protected $comments;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", nullable=false)
     */
    private $title;

    /**
     * @var int|null
     *
     * @ORM\Column(name="author_id", type="integer", nullable=true)
     */
    private $authorId;

    /**
     * Flag which shows if the blog is active or not. 1= active otherwise inactive
     *
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active;

    /**
     * @var string
     *
     * @ORM\Column(name="short_description", type="string", nullable=false)
     */
    private $shortDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var int|null
     *
     * @ORM\Column(name="views", type="integer", nullable=true)
     */
    private $views;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(name="display_date", type="datetime", nullable=false)
     */
    private $displayDate;

    /**
     * @var int|null
     *
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     */
    private $categoryId;

    /**
     * @var Category|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Category\Category")
     * @ORM\JoinColumn(name="category_id", referencedColumnName="id")
     */
    private $category;

    /**
     * @var string
     *
     * @ORM\Column(name="template", type="string", nullable=false)
     */
    private $template;

    /**
     * @var string|null
     *
     * @ORM\Column(name="meta_keywords", type="text", nullable=true)
     */
    private $metaKeyWords;

    /**
     * @var string|null
     *
     * @ORM\Column(name="meta_description", type="string", nullable=true)
     */
    private $metaDescription;

    /**
     * @var string|null
     *
     * @ORM\Column(name="meta_title", type="string", nullable=true)
     */
    private $metaTitle;

    /**
     * @var string|null
     *
     * @ORM\Column(name="shop_ids", type="string", nullable=true)
     */
    private $shopIds;

    /**
     * INVERSE SIDE
     *
     * @var User|null
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\User\User", inversedBy="blog")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id", nullable=true)
     */
    private $author;

    public function __construct()
    {
        $this->media = new ArrayCollection();
        $this->comments = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->assignedArticles = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
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
     * @return Category|null
     */
    public function getCategory()
    {
        return $this->category;
    }

    /**
     * @param Category|null $category
     */
    public function setCategory($category)
    {
        $this->category = $category;
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
     * @return string
     */
    public function getShortDescription()
    {
        return $this->shortDescription;
    }

    /**
     * @param string $shortDescription
     */
    public function setShortDescription($shortDescription)
    {
        $this->shortDescription = $shortDescription;
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
     * @return int|null
     */
    public function getViews()
    {
        return $this->views;
    }

    /**
     * @param int $views
     */
    public function setViews($views)
    {
        $this->views = $views;
    }

    /**
     * @return DateTimeInterface
     */
    public function getDisplayDate()
    {
        return $this->displayDate;
    }

    /**
     * @param DateTimeInterface|string $displayDate
     */
    public function setDisplayDate($displayDate)
    {
        if (!$displayDate instanceof DateTimeInterface && $displayDate !== '') {
            $displayDate = new DateTime($displayDate);
        }
        $this->displayDate = $displayDate;
    }

    /**
     * @return int|null
     */
    public function getCategoryId()
    {
        return $this->categoryId;
    }

    /**
     * @param int $categoryId
     */
    public function setCategoryId($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    /**
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param ArrayCollection $tags
     */
    public function setTags($tags)
    {
        $this->tags = $tags;
    }

    /**
     * @return string
     */
    public function getTemplate()
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate($template)
    {
        $this->template = $template;
    }

    /**
     * @return ArrayCollection
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param Media[]|null $media
     *
     * @return Blog|ModelEntity
     */
    public function setMedia($media)
    {
        return $this->setOneToMany($media, Media::class, 'media', 'blog');
    }

    /**
     * Get the assigned articles
     *
     * @return ArrayCollection
     */
    public function getAssignedArticles()
    {
        return $this->assignedArticles;
    }

    /**
     * Set the assigned articles
     *
     * @param ArrayCollection $assignedArticles
     */
    public function setAssignedArticles($assignedArticles)
    {
        $this->assignedArticles = $assignedArticles;
    }

    /**
     * Set the metaKeyWords
     *
     * @return string|null
     */
    public function getMetaKeyWords()
    {
        return $this->metaKeyWords;
    }

    /**
     * Get the metaKeyWords
     *
     * @param string|null $metaKeyWords
     */
    public function setMetaKeyWords($metaKeyWords)
    {
        $this->metaKeyWords = $metaKeyWords;
    }

    /**
     * Get the metaDescription
     *
     * @return string|null
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * Set the metaDescription
     *
     * @param string|null $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * Returns the Attributes
     *
     * @return BlogAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * Returns the blog attribute
     *
     * @param BlogAttribute|array|null $attribute
     *
     * @return BlogAttribute|ModelEntity
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, BlogAttribute::class, 'attribute', 'blog');
    }

    /**
     * @return ArrayCollection
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param Comment[]|null $comments
     *
     * @return Blog|ModelEntity
     */
    public function setComments($comments)
    {
        return $this->setOneToMany($comments, Comment::class, 'comments', 'blog');
    }

    /**
     * Returns the author
     *
     * @return User|null
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Sets the author
     *
     * @param User|null $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Set the metaTitle
     *
     * @param string|null $metaTitle
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * Returns the metaTitle
     *
     * @return string|null
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * Returns the unexploded shop ids string (ex: |1|2|)
     */
    public function getShopIds(): ?string
    {
        return $this->shopIds;
    }

    /**
     * Set the unexploded shop ids string (ex: |1|2|)
     */
    public function setShopIds(?string $shopIds = null): Blog
    {
        $this->shopIds = $shopIds;

        return $this;
    }
}
