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

namespace Shopware\Models\Premium;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\LazyFetchModelEntity;

/**
 * Shopware Model Premium
 *
 * This is the model for the premium-module, which contains a single row from s_addon_premiums.
 * There are two associations to the shop-model(1:1 - Shopware\Models\Shop\Shop) and to the article-model(Shopware\Models\Article\Detail).
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_addon_premiums")
 * @ORM\HasLifecycleCallbacks()
 */
class Premium extends LazyFetchModelEntity
{
    /**
     * @var \Shopware\Models\Shop\Shop
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="subshopID", referencedColumnName="id")
     */
    protected $shop;

    /**
     * @var \Shopware\Models\Article\Detail
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Detail")
     * @ORM\JoinColumn(name="ordernumber", referencedColumnName="ordernumber")
     */
    protected $articleDetail;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var float
     *
     * @ORM\Column(name="startprice", type="decimal", nullable=false)
     */
    private $startPrice;

    /**
     * @var string
     *
     * @ORM\Column(name="ordernumber", type="string", length=255, nullable=false)
     */
    private $orderNumber;

    /**
     * @var string
     *
     * @ORM\Column(name="ordernumber_export", type="string", length=255, nullable=false)
     */
    private $orderNumberExport;

    /**
     * @var int
     *
     * @ORM\Column(name="subshopID", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * Returns the primary-key id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the startprice for a premium-article
     *
     * @param float $startPrice
     *
     * @return Premium
     */
    public function setStartPrice($startPrice)
    {
        $this->startPrice = $startPrice;

        return $this;
    }

    /**
     * Returns the startprice of a premium-article
     *
     * @return float
     */
    public function getStartPrice()
    {
        return $this->startPrice;
    }

    /**
     * Sets the ordernumber for a premium-article
     *
     * @param string $orderNumber
     *
     * @return Premium
     */
    public function setOrderNumber($orderNumber)
    {
        $this->orderNumber = $orderNumber;

        return $this;
    }

    /**
     * Gets the orderNumber of a premium-article
     *
     * @return string
     */
    public function getOrderNumber()
    {
        return $this->orderNumber;
    }

    /**
     * Sets the pseudoId for a premium-article
     *
     * @param string $orderNumberExport
     *
     * @return Premium
     */
    public function setOrderNumberExport($orderNumberExport)
    {
        $this->orderNumberExport = $orderNumberExport;

        return $this;
    }

    /**
     * Gets the pseudoId of a premium-article
     *
     * @return string
     */
    public function getOrderNumberExport()
    {
        return $this->orderNumberExport;
    }

    /**
     * Sets the assigned subShop
     *
     * @param \Shopware\Models\Shop\Shop $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }

    /**
     * Returns the instance of the assigned subShop
     *
     * @return \Shopware\Models\Shop\Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * Sets the assigned article
     *
     * @param \Shopware\Models\Article\Detail $articleDetail
     *
     * @return \Shopware\Models\Premium\Premium
     */
    public function setArticleDetail($articleDetail)
    {
        $this->articleDetail = $articleDetail;

        return $this;
    }

    /**
     * Gets the instance of the assigned article
     *
     * @return \Shopware\Models\Article\Detail
     */
    public function getArticleDetail()
    {
        /** @var \Shopware\Models\Article\Detail $return */
        $return = $this->fetchLazy($this->articleDetail, ['number' => $this->orderNumber]);

        return $return;
    }

    /**
     * Sets the shopId of a premium-article
     *
     * @param int $shopId Contains the shopId
     *
     * @return \Shopware\Models\Premium\Premium
     */
    public function setShopId($shopId)
    {
        $this->shopId = $shopId;

        return $this;
    }

    /**
     * Sets the shopId of a premium-article
     *
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }
}
