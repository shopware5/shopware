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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

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
     * @var ArrayCollection<\Shopware\Models\Blog\Tag>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Blog\Tag", mappedBy="blog", orphanRemoval=true)
     */
    protected $tags;

    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Blog\Media>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Blog\Media", mappedBy="blog", orphanRemoval=true, cascade={"persist"})
     */
    protected $media;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Article\Article>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinTable(name="s_blog_assigned_articles",
     *     joinColumns={@ORM\JoinColumn(name="blog_id", referencedColumnName="id")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="article_id", referencedColumnName="id")}
     * )
     */
    protected $assignedArticles;

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\Attribute\Blog
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Blog", mappedBy="blog", cascade={"persist"})
     */
    protected $attribute;

    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Blog\Comment>
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
     * @var int
     *
     * @ORM\Column(name="author_id", type="integer", nullable=true)
     */
    private $authorId = null;

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
     * @var int
     *
     * @ORM\Column(name="views", type="integer", nullable=true)
     */
    private $views = null;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="display_date", type="datetime", nullable=false)
     */
    private $displayDate;

    /**
     * @var int
     *
     * @ORM\Column(name="category_id", type="integer", nullable=true)
     */
    private $categoryId = null;

    /**
     * @var \Shopware\Models\Category\Category
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
     * @var string
     *
     * @ORM\Column(name="meta_keywords", type="string", nullable=true)
     */
    private $metaKeyWords;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="string", nullable=true)
     */
    private $metaDescription;

    /**
     * @var string
     *
     * @ORM\Column(name="meta_title", type="string", nullable=true)
     */
    private $metaTitle;

    /**
     * @var string
     *
     * @ORM\Column(name="shop_ids", type="string", nullable=false)
     */
    private $shopIds;

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\User\User
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\User\User", inversedBy="blog")
     * @ORM\JoinColumn(name="author_id", referencedColumnName="id")
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
     * @return int
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
     * @return \DateTimeInterface
     */
    public function getDisplayDate()
    {
        return $this->displayDate;
    }

    /**
     * @param \DateTimeInterface|string $displayDate
     */
    public function setDisplayDate($displayDate)
    {
        if (!$displayDate instanceof \DateTimeInterface && strlen($displayDate) > 0) {
            $displayDate = new \DateTime($displayDate);
        }
        $this->displayDate = $displayDate;
    }

    /**
     * @return int
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
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getMedia()
    {
        return $this->media;
    }

    /**
     * @param \Shopware\Models\Blog\Media[]|null $media
     *
     * @return Blog|ModelEntity
     */
    public function setMedia($media)
    {
        return $this->setOneToMany($media, \Shopware\Models\Blog\Media::class, 'media', 'blog');
    }

    /**
     * Get the assigned articles
     *
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Article\Article>
     */
    public function getAssignedArticles()
    {
        return $this->assignedArticles;
    }

    /**
     * Set the assigned articles
     *
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Article\Article> $assignedArticles
     */
    public function setAssignedArticles($assignedArticles)
    {
        $this->assignedArticles = $assignedArticles;
    }

    /**
     * Set the metaKeyWords
     *
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
     *
     * @return \Shopware\Models\Attribute\Blog|ModelEntity
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, \Shopware\Models\Attribute\Blog::class, 'attribute', 'blog');
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Blog\Comment>
     */
    public function getComments()
    {
        return $this->comments;
    }

    /**
     * @param \Shopware\Models\Blog\Comment[]|null $comments
     *
     * @return Blog|ModelEntity
     */
    public function setComments($comments)
    {
        return $this->setOneToMany($comments, \Shopware\Models\Blog\Comment::class, 'comments', 'blog');
    }

    /**
     * Returns the author
     *
     * @return \Shopware\Models\User\User
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Sets the author
     *
     * @param \Shopware\Models\User\User $author
     */
    public function setAuthor($author)
    {
        $this->author = $author;
    }

    /**
     * Set the metaTitle
     *
     * @param string $metaTitle
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * Returns the metaTitle
     *
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * Returns the unexploded shop ids string (ex: |1|2|)
     */
    public function getShopIds(): string
    {
        return $this->shopIds;
    }

    /**
     * Set the unexploded shop ids string (ex: |1|2|)
     */
    public function setShopIds(string $shopIds = null): Blog
    {
        $this->shopIds = $shopIds;

        return $this;
    }
}
