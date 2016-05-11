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

use Shopware\Components\Model\LazyFetchModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * @category  Shopware
 * @package   Shopware\Models\Article
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_articles_prices")
 * @ORM\HasLifecycleCallbacks
 */
class Price extends LazyFetchModelEntity
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
     * @var integer $articleId
     * @ORM\Column(name="articleID", type="integer", nullable=false)
     */
    private $articleId;

    /**
     * @var integer $articleDetailsId
     * @ORM\Column(name="articledetailsID", type="integer", nullable=false)
     */
    private $articleDetailsId;

    /**
     * @var string $customerGroupKey
     * @ORM\Column(name="pricegroup", type="string", length=30, nullable=false)
     */
    private $customerGroupKey = '';

    /**
     * @var integer $from
     *
     * @ORM\Column(name="`from`", type="integer", nullable=false)
     */
    private $from = 0;

    /**
     * @var integer $to
     *
     * @ORM\Column(name="`to`", type="string", nullable=true)
     */
    private $to = 'beliebig';

    /**
     * @var float $price
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     */
    private $price = 0;

    /**
     * @var float $pseudoPrice
     *
     * @ORM\Column(name="pseudoprice", type="float", nullable=false)
     */
    private $pseudoPrice = 0;

    /**
     * @var float $basePrice
     * @deprecated 5.2.0 No longer used by internal code, use Shopware\Models\Article\Detail::purchasePrice instead.
     *
     * @ORM\Column(name="baseprice", type="float", nullable=false)
     */
    private $basePrice = 0;

    /**
     * @var float $percent
     *
     * @ORM\Column(name="percent", type="float", nullable=false)
     */
    private $percent = 0;

    /**
     * OWNING SIDE
     * @var \Shopware\Models\Article\Detail
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Detail", inversedBy="prices")
     * @ORM\JoinColumn(name="articledetailsID", referencedColumnName="id")
     * @ORM\OrderBy({"customerGroupKey" = "ASC", "from" = "ASC"})
     */
    protected $detail;

    /**
     * @var \Shopware\Models\Customer\Group $customerGroup
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Customer\Group")
     * @ORM\JoinColumn(name="pricegroup", referencedColumnName="groupkey")
     */
    private $customerGroup;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ArticlePrice", orphanRemoval=true, mappedBy="articlePrice", cascade={"persist"})
     * @var \Shopware\Models\Attribute\ArticlePrice
     */
    protected $attribute;

    /**
     * OWNING SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     * @var \Shopware\Models\Article\Article
     */
    protected $article;

    /**
     * @return \Shopware\Models\Article\Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param  \Shopware\Models\Article\Article|array|null $article
     * @return \Shopware\Models\Article\Price
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
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
     * Set customerGroup
     *
     * @param  \Shopware\Models\Customer\Group $customerGroup
     * @return Price
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;

        return $this;
    }

    /**
     * Get customerGroup
     *
     * @return \Shopware\Models\Customer\Group
     */
    public function getCustomerGroup()
    {
        return $this->fetchLazy($this->customerGroup, array('key' => $this->customerGroupKey));
    }

    /**
     * Set from
     *
     * @param  integer $from
     * @return Price
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Get from
     *
     * @return integer
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set to
     *
     * @param  int|null $to
     * @return Price
     */
    public function setTo($to)
    {
        if ($to === null) {
            $to = 'beliebig';
        }
        $this->to = $to;

        return $this;
    }

    /**
     * Get to
     *
     * @return integer|null
     */
    public function getTo()
    {
        return $this->to < 0 ? null : $this->to;
    }

    /**
     * Set article detail id
     *
     * @param  Detail $detail
     * @return Price
     */
    public function setDetail($detail)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * Get article detail id
     *
     * @return Detail
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Set price
     *
     * @param  float $price
     * @return Price
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * Get price
     *
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * Set pseudoPrice
     *
     * @param  float $pseudoPrice
     * @return Price
     */
    public function setPseudoPrice($pseudoPrice)
    {
        $this->pseudoPrice = $pseudoPrice;

        return $this;
    }

    /**
     * Get pseudoPrice
     *
     * @return float
     */
    public function getPseudoPrice()
    {
        return $this->pseudoPrice;
    }

    /**
     * Set basePrice
     *
     * @deprecated 5.2.0 No longer used by internal code, use Shopware\Models\Article\Detail::setPurchasePrice() instead.
     * @param  float $basePrice
     * @return Price
     */
    public function setBasePrice($basePrice)
    {
        $this->basePrice = $basePrice;

        return $this;
    }

    /**
     * Get basePrice
     *
     * @deprecated 5.2.0 No longer used by internal code, use Shopware\Models\Article\Detail::getPurchasePrice() instead.
     * @return float
     */
    public function getBasePrice()
    {
        return $this->basePrice;
    }

    /**
     * Set percent
     *
     * @param  float $percent
     * @return Price
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * Get percent
     *
     * @return float
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @return \Shopware\Models\Attribute\ArticlePrice
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param  \Shopware\Models\Attribute\ArticlePrice|array|null $attribute
     * @return \Shopware\Components\Model\ModelEntity
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\ArticlePrice', 'attribute', 'articlePrice');
    }
}
