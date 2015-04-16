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

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;

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
     * Autoincrement ID
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Name of the supplier
     *
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * Logo for that supplier. Utilize the media manager
     *
     * @var string $image
     *
     * @ORM\Column(name="img", type="string", nullable=true)
     */
    private $image = '';

    /**
     * Link to the suppliers homepage
     *
     * @var string $link
     *
     * @ORM\Column(name="link", type="string", nullable=true)
     */
    private $link = '';

    /**
     * Description text which can be used e.g. for a special supplier page
     *
     * @var string $link
     *
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * Title for the page - SEO metadata
     *
     * @var string $metaTitle
     *
     * @ORM\Column(name="meta_title", type="string", nullable=true)
     */
    protected $metaTitle;

    /**
     * Description for the page - SEO metadata
     *
     * @var string $metaDescription
     *
     * @ORM\Column(name="meta_description", type="string", nullable=true)
     */
    protected $metaDescription;

    /**
     * Meta keywords for the page - SEO metadata
     *
     * @var string $metaKeywords
     *
     * @ORM\Column(name="meta_keywords", type="string", nullable=true)
     */
    protected $metaKeywords;

    /**
     * @var \DateTime $changed
     *
     * @ORM\Column(name="changed", type="datetime", nullable=false)
     */
    private $changed;

   /**
    * INVERSE SIDE
    * Articles can be bound to a specific supplier
    *
    * @ORM\OneToMany(targetEntity="Shopware\Models\Article\Article", mappedBy="supplier", fetch="EXTRA_LAZY")
    * @ORM\JoinColumn(name="id", referencedColumnName="supplierID")
    */
    protected $articles;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ArticleSupplier", mappedBy="articleSupplier", cascade={"persist"})
     * @var \Shopware\Models\Attribute\ArticleSupplier
     */
    protected $attribute;

    public function __construct()
    {
        $this->articles = new ArrayCollection();
        $this->changed = new \DateTime();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set the supplier name
     *
     * @param string $name
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
     * @return \Doctrine\Common\Collections\ArrayCollection $articles
     */
    public function getArticles()
    {
        return $this->articles;
    }

    /**
     * Takes an array of articles, in most cases doctrine will take care of this.
     *
     * @param \Doctrine\Common\Collections\ArrayCollection $articles $articles
     * @return Supplier
     */
    public function setArticles($articles)
    {
        $this->articles = $articles;
        return $this;
    }

    /**
     * @return \Shopware\Models\Attribute\ArticleSupplier
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\ArticleSupplier|array|null $attribute
     * @return \Shopware\Models\Attribute\ArticleSupplier
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\ArticleSupplier', 'attribute', 'articleSupplier');
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
     * Set changed
     *
     * @param \DateTime|string $changed
     * @return Supplier
     */
    public function setChanged($changed = 'now')
    {
        if (!$changed instanceof \DateTime) {
            $this->changed = new \DateTime($changed);
        } else {
            $this->changed = $changed;
        }
        return $this;
    }

    /**
     * Get changed
     *
     * @return \DateTime
     */
    public function getChanged()
    {
        return $this->changed;
    }
}
