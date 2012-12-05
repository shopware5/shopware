<?php
/**
 * Shopware 4.0
 * Copyright Â© 2012 shopware AG
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

namespace Shopware\CustomModels\LiveShopping;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;

/**
 * @category Shopware
 * @package Shopware\Plugins\SwagLiveShopping\Models\LiveShopping
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity
 * @ORM\Table(name="s_articles_live_prices")
 */
class Price extends ModelEntity
{
    /**
     * @var integer $id
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     * @ORM\Column(name="id", type="integer", nullable=false)
     */
    protected $id;

    /**
     * @var integer $liveShoppingId
     *
     * @ORM\Column(name="live_shopping_id", type="integer", nullable=true)
     */
    protected $liveShoppingId;

    /**
     * @var float $price
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     */
    protected $price;


    /**
     * @var float $endPrice
     *
     * @ORM\Column(name="endprice", type="float", nullable=false)
     */
    protected $endPrice;

    /**
     * @ORM\ManyToOne(targetEntity="Shopware\CustomModels\LiveShopping\LiveShopping", inversedBy="prices")
     * @ORM\JoinColumn(name="live_shopping_id", referencedColumnName="id")
     * @var \Shopware\CustomModels\LiveShopping\LiveShopping
     */
    protected $liveShopping;

    /**
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id")
     * @var \Shopware\Models\Customer\Group
     */
    protected $customerGroup;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param $id
     *
     * @return Price
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * @return int
     */
    public function getLiveShoppingId()
    {
        return $this->liveShoppingId;
    }

    /**
     * @param $liveShoppingId
     * @return Price
     */
    public function setLiveShoppingId($liveShoppingId)
    {
        $this->liveShoppingId = $liveShoppingId;
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
     * @param $price
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
    public function getEndPrice()
    {
        return $this->endPrice;
    }

    /**
     * @param $endPrice
     * @return Price
     */
    public function setEndPrice($endPrice)
    {
        $this->endPrice = $endPrice;
        return $this;
    }

    /**
     * @return \Shopware\CustomModels\LiveShopping\LiveShopping
     */
    public function getLiveShopping()
    {
        return $this->liveShopping;
    }

    /**
     * @param $liveShopping
     */
    public function setLiveShopping($liveShopping)
    {
        $this->liveShopping = $liveShopping;
    }

    /**
     * @return \Shopware\Models\Customer\Group
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param \Shopware\Models\Customer\Group $customerGroup
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }
}