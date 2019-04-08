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

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\LazyFetchModelEntity;
use Shopware\Models\Attribute\ArticlePrice as ProductPriceAttribute;
use Shopware\Models\Customer\Group as CustomerGroup;

/**
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_articles_prices")
 */
class Price extends LazyFetchModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var Detail
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Detail", inversedBy="prices")
     * @ORM\JoinColumn(name="articledetailsID", referencedColumnName="id")
     * @ORM\OrderBy({"customerGroupKey" = "ASC", "from" = "ASC"})
     */
    protected $detail;

    /**
     * INVERSE SIDE
     *
     * @var ProductPriceAttribute
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\ArticlePrice", orphanRemoval=true, mappedBy="articlePrice", cascade={"persist"})
     */
    protected $attribute;

    /**
     * OWNING SIDE
     *
     * @var Article
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Article")
     * @ORM\JoinColumn(name="articleID", referencedColumnName="id")
     */
    protected $article;

    /**
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
     * @ORM\Column(name="articleID", type="integer", nullable=false)
     */
    private $articleId;

    /**
     * @var int
     *
     * @ORM\Column(name="articledetailsID", type="integer", nullable=false)
     */
    private $articleDetailsId;

    /**
     * @var string
     *
     * @ORM\Column(name="pricegroup", type="string", length=15, nullable=false)
     */
    private $customerGroupKey = '';

    /**
     * @var int
     *
     * @ORM\Column(name="`from`", type="integer", nullable=false)
     */
    private $from = 1;

    /**
     * @var int|string
     *
     * @ORM\Column(name="`to`", type="string", nullable=true)
     */
    private $to = 'beliebig';

    /**
     * @var float
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     */
    private $price = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="pseudoprice", type="float", nullable=false)
     */
    private $pseudoPrice = 0;

    /**
     * @var float
     *
     * @ORM\Column(name="percent", type="float", nullable=false)
     */
    private $percent = 0;

    /**
     * @var CustomerGroup
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Customer\Group")
     * @ORM\JoinColumn(name="pricegroup", referencedColumnName="groupkey")
     */
    private $customerGroup;

    /**
     * @return Article
     */
    public function getArticle()
    {
        return $this->article;
    }

    /**
     * @param Article|array|null $article
     *
     * @return Price
     */
    public function setArticle($article)
    {
        $this->article = $article;

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param CustomerGroup $customerGroup
     *
     * @return Price
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;

        return $this;
    }

    /**
     * @return CustomerGroup
     */
    public function getCustomerGroup()
    {
        /** @var CustomerGroup $return */
        $return = $this->fetchLazy($this->customerGroup, ['key' => $this->customerGroupKey]);

        return $return;
    }

    /**
     * @param int $from
     *
     * @return Price
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return int
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param int|string|null $to
     *
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
     * @return int|null
     */
    public function getTo()
    {
        return $this->to < 0 ? null : $this->to;
    }

    /**
     * Set article detail id
     *
     * @param Detail $detail
     *
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
     * @param float $price
     *
     * @return Price
     */
    public function setPrice($price)
    {
        $this->price = $price;

        return $this;
    }

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $pseudoPrice
     *
     * @return Price
     */
    public function setPseudoPrice($pseudoPrice)
    {
        $this->pseudoPrice = $pseudoPrice;

        return $this;
    }

    /**
     * @return float
     */
    public function getPseudoPrice()
    {
        return $this->pseudoPrice;
    }

    /**
     * @param float $percent
     *
     * @return Price
     */
    public function setPercent($percent)
    {
        $this->percent = $percent;

        return $this;
    }

    /**
     * @return float
     */
    public function getPercent()
    {
        return $this->percent;
    }

    /**
     * @return ProductPriceAttribute
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param ProductPriceAttribute|array|null $attribute
     *
     * @return Price
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, ProductPriceAttribute::class, 'attribute', 'articlePrice');
    }
}
