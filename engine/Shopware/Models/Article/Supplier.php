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

namespace Shopware\Models\Article;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\ArticleSupplier as ProductSupplierAttribute;

/**
 * Supplier Model
 * <br>
 * This Model represents a single supplier. Any article can be bound to one supplier.
 * If there is at least one article bound to the supplier, the supplier MUST not be deleted!
 *
 *
 * Relations and Associations
 * <code>
 * - Article    =>  Shopware\Models\Article\Article     [1:n] [s_articles]
 * </code>
 *
 * Indices for s_articles_supplier:
 * <code>
 *   - PRIMARY KEY (`id`)
 * </code>
 *
 * @ORM\Entity(repositoryClass="SupplierRepository")
 * @ORM\Table(name="s_articles_supplier")
 */
class Supplier extends ModelEntity
{
    /**
     * Title for the page - SEO metadata
     *
     * @var string
     *
     * @ORM\Column(name="meta_title", type="string", nullable=true)
     */
    protected $metaTitle;

    /**
     * Description for the page - SEO metadata
     *
     * @var string
     *
     * @ORM\Column(name="meta_description", type="string", nullable=true)
     */
    protected $metaDescription;

    /**
     * Meta keywords for the page - SEO metadata
     *
     * @var string
     *
     * @ORM\Column(name="meta_keywords", type="string", nullable=true)
     */
    protected $metaKeywords;

    /**
     * INVERSE SIDE
     * Articles can be bound to a specific supplier
     *
     * @var ArrayCollection<\Shopware\Models\Article\Article>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Article", mappedBy="supplier", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="id", referencedColumnName="supplierID")
     */
    protected $articles;

    /**
     * INVERSE SIDE
     *
     * @var ProductSupplierAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ArticleSupplier", mappedBy="articleSupplier", cascade={"persist"})
     */
    protected $attribute;

    /**
     * Autoincrement ID
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Name of the supplier
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * Logo for that supplier. Utilize the media manager
     *
     * @var string
     *
     * @ORM\Column(name="img", type="string", nullable=false)
     */
    private $image = '';

    /**
     * Link to the suppliers homepage
     *
     * @var string
     *
     * @ORM\Column(name="link", type="string", nullable=false)
     */
    private $link = '';

    /**
     * Description text which can be used e.g. for a special supplier page
     *
     * @var string
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="changed", type="datetime", nullable=false)
     */
    private $changed;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->changed = new \DateTime();
    }

    /**
     * Sets the primary key
     *
     * @param int $id
     */
    public function setPrimaryIdentifier($id)
    {
        $this->id = (int) $id;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the supplier name
     *
     * @param string $name
     *
     * @return Supplier
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the name of the supplier
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set the path to the suppliers logo
     *
     * @param string $image
     *
     * @return Supplier
     */
    public function setImage($image)
    {
        $this->image = $image;

        return $this;
    }

    /**
     * Returns the path to the suppliers logo
     *
     * @return string
     */
    public function getImage()
    {
        return $this->image;
    }

    /**
     * Takes an URL and saves it. It use the method "standardizeUrl" to assure that the
     * URL will be prefixed with 'http://' if it not already provided.
     *
     * @param string $link
     *
     * @return Supplier
     */
    public function setLink($link)
    {
        $this->link = $link;

        return $this;
    }

    /**
     * Returns the link to the suppliers homepage. If there is no http in front
     * of the url, the http will be added through the internal method "standardizeUrl"
     *
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * Returns suppliers description. This description may contains HTML Code.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Sets the description of the supplier. This description may contains HTML codes.
     *
     * @param string $description
     *
     * @return Supplier
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Returns all articles assigned to this supplier
     *
     * @return ArrayCollection<\Shopware\Models\Article\Article>
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * Takes an array of articles, in most cases doctrine will take care of this.
     *
     * @param ArrayCollection<\Shopware\Models\Article\Article> $articles
     *
     * @return Supplier
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;

        return $this;
    }

    /**
     * @return ProductSupplierAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param ProductSupplierAttribute|array|null $attribute
     *
     * @return Supplier
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, ProductSupplierAttribute::class, 'attribute', 'articleSupplier');
    }

    /**
     * @param string $metaTitle
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
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
     * @param \DateTimeInterface|string $changed
     *
     * @return Supplier
     */
    public function setChanged($changed = 'now')
    {
        if (!$changed instanceof \DateTimeInterface) {
            $this->changed = new \DateTime($changed);
        } else {
            $this->changed = $changed;
        }

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getChanged()
    {
        return $this->changed;
    }
}
