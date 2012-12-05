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

namespace Shopware\CustomModels\Bundle;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;

/**
 * Price model of the bundle plugin.
 * The price model contains the definition of a single bundle price for a single customer group.
 * Each customer group, which has a price and added in the Shopware\CustomModels\Bundle\Bundle::customerGroups property,
 * can buy/see the bundle in the store front.
 * The price contains only the assinged bundle, customer group and the custom inserted price.
 *
 * @category Shopware
 * @package Shopware\Plugins\SwagBundle\Models\Bundle
 * @copyright Copyright (c) 2012, shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity
 * @ORM\Table(name="s_articles_bundles_prices")
 */
class Price extends ModelEntity
{
    /**
     * Unique identifier for a single bundle article
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Identifier for the assigned bundle.
     * Used as foreign key for the bundle association.
     * Has no getter and setter.
     * Only defined to have access on the bundle id in queries without joining the s_articles_bundles.
     *
     * @ORM\Column(name="bundle_id", type="integer", nullable=false)
     * @var int $bundleId
     */
    private $bundleId;

    /**
     * Identifier for the assigned customer group of the defined price.
     * Used as foreign key for the customer group association.
     * Has no getter and setter.
     * Only defined to have access on the customer group key in queries without joining the s_core_customergroups.
     *
     * @ORM\Column(name="customer_group_id", type="integer", nullable=false)
     * @var int $customerGroupId
     */
    private $customerGroupId;

    /**
     * The defined custom bundle price.
     * Defined over the backend module.
     *
     * @ORM\Column(name="price", type="float", nullable=false)
     * @var float $price
     */
    private $price;

    /**
     * OWNING SIDE
     * The $bundle property contains the assigned instance of \Shopware\CustomModels\Bundle\Bundle.
     *
     * @ORM\ManyToOne(targetEntity="Shopware\CustomModels\Bundle\Bundle", inversedBy="prices")
     * @ORM\JoinColumn(name="bundle_id", referencedColumnName="id")
     * @var \Shopware\CustomModels\Bundle\Bundle
     */
    protected $bundle;

    /**
     * Contains the defined customer group model instance (\Shopware\Models\Customer\Group) on which the price
     * defined.
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Group")
     * @ORM\JoinColumn(name="customer_group_id", referencedColumnName="id")
     * @var \Shopware\Models\Customer\Group
     */
    protected $customerGroup;

    /**
     * Class property which contains the price which has to been displayed in the store front.
     * @var float
     */
    private $displayPrice;

    /**
     * Class property which contains the net price.
     * @var float
     */
    private $netPrice;

    /**
     * Class property which contains the percentage value for the bundle price.
     * @var int
     */
    private $percentage;

    /**
     * Class property which contains the gross price.
     * @var
     */
    private $grossPrice;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return \Shopware\CustomModels\Bundle\Bundle
     */
    public function getBundle()
    {
        return $this->bundle;
    }

    /**
     * @param \Shopware\CustomModels\Bundle\Bundle $bundle
     */
    public function setBundle($bundle)
    {
        $this->bundle = $bundle;
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

    /**
     * @return float
     */
    public function getPrice()
    {
        return $this->price;
    }

    /**
     * @param float $price
     */
    public function setPrice($price)
    {
        $this->price = $price;
    }

    /**
     * @return float
     */
    public function getDisplayPrice()
    {
        return $this->displayPrice;
    }

    /**
     * @param float $displayPrice
     */
    public function setDisplayPrice($displayPrice)
    {
        $this->displayPrice = $displayPrice;
    }

    /**
     * @return float
     */
    public function getNetPrice()
    {
        return $this->netPrice;
    }

    /**
     * @param float $netPrice
     */
    public function setNetPrice($netPrice)
    {
        $this->netPrice = $netPrice;
    }

    /**
     * @return int
     */
    public function getPercentage()
    {
        return $this->percentage;
    }

    /**
     * @param int $percentage
     */
    public function setPercentage($percentage)
    {
        $this->percentage = $percentage;
    }

    /**
     * @return
     */
    public function getGrossPrice()
    {
        return $this->grossPrice;
    }

    /**
     * @param  $grossPrice
     */
    public function setGrossPrice($grossPrice)
    {
        $this->grossPrice = $grossPrice;
    }

}
