<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

namespace Shopware\Models\Attribute;

use Doctrine\ORM\Mapping as ORM,
    Shopware\Components\Model\ModelEntity;

/**
 * Shopware\Models\Attribute\ArticlePrice
 *
 * @ORM\Table(name="s_articles_prices_attributes")
 * @ORM\Entity
 */
class ArticlePrice extends ModelEntity
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
     * @var integer $articlePriceId
     *
     * @ORM\Column(name="priceID", type="integer", nullable=true)
     */
    private $articlePriceId = null;

    /**
     * @var Shopware\Models\Article\Price
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Price", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="priceID", referencedColumnName="id")
     * })
     */
    private $articlePrice;

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
     * Set articlePrice
     *
     * @param Shopware\Models\Article\Price $articlePrice
     * @return ArticlePrice
     */
    public function setArticlePrice(\Shopware\Models\Article\Price $articlePrice = null)
    {
        $this->articlePrice = $articlePrice;
        return $this;
    }

    /**
     * Get articlePrice
     *
     * @return Shopware\Models\Article\Price
     */
    public function getArticlePrice()
    {
        return $this->articlePrice;
    }

    /**
     * Set articlePriceId
     *
     * @param integer $articlePriceId
     * @return ArticlePrice
     */
    public function setArticlePriceId($articlePriceId)
    {
        $this->articlePriceId = $articlePriceId;
        return $this;
    }

    /**
     * Get articlePriceId
     *
     * @return integer
     */
    public function getArticlePriceId()
    {
        return $this->articlePriceId;
    }
}
