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

namespace Shopware\Models\Customer;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\CustomerGroup;

/**
 * Shopware customer group model represents a single customer group.
 *
 * The Shopware customer group model represents a row of the s_core_customergroups table.
 * The group model data set from the Shopware\Models\Customer\Repository.
 * One group has the follows associations:
 * <code>
 *   - Customer =>  Shopware\Models\Customer\Customer [1:1] [s_user]
 * </code>
 * The s_core_customergroups table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - KEY `groupKey` (`groupKey`)
 * </code>
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_core_customergroups")
 */
class Group extends ModelEntity
{
    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Customer\Discount>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Customer\Discount", mappedBy="group", orphanRemoval=true, cascade={"persist"})
     */
    protected $discounts;

    /**
     * INVERSE SIDE
     *
     * @var CustomerGroup
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\CustomerGroup", mappedBy="customerGroup", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * INVERSE SIDE
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Customer\Customer>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Customer\Customer", mappedBy="group")
     */
    protected $customers;

    /**
     * The id property is an identifier property which means
     * doctrine associations can be defined over this field.
     * Column property for the database field id.
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains a alphanumeric key for the group.
     *
     * @var string
     *
     * @ORM\Column(name="groupkey", type="string", length=15, nullable=false)
     */
    private $key;

    /**
     * Contains the customer group description value.
     * Column property for the database field description
     *
     * @var string
     *
     * @ORM\Column(name="description", type="string", length=30, nullable=false)
     */
    private $name;

    /**
     * Contains the tax value for the customer group.
     * Column property for the database field tax
     *
     * @var bool
     *
     * @ORM\Column(name="tax", type="boolean", nullable=false)
     */
    private $tax;

    /**
     * Contains the customer group tax input.
     * Column property for the database field taxinput
     *
     * @var bool
     *
     * @ORM\Column(name="taxinput", type="boolean", nullable=false)
     */
    private $taxInput;

    /**
     * Contains the mode of the customer group.
     * Column property for the database field mode
     *
     * @var bool
     *
     * @ORM\Column(name="mode", type="boolean", nullable=false)
     */
    private $mode;

    /**
     * Contains the discount value of the customer group.
     * Column property for the database field discount
     *
     * @var float
     *
     * @ORM\Column(name="discount", type="float", nullable=false)
     */
    private $discount = 0;

    /**
     * Contains the minimum value of an order for this customer group.
     * Column property for the database field minimumorder
     *
     * @var float
     *
     * @ORM\Column(name="minimumorder", type="float", nullable=false)
     */
    private $minimumOrder = 0;

    /**
     * Contains the minimum surcharge value of an order for this customer group.
     * Column property for the database field minimumordersurcharge
     *
     * @var float
     *
     * @ORM\Column(name="minimumordersurcharge", type="float", nullable=false)
     */
    private $minimumOrderSurcharge = 0;

    public function __construct()
    {
        $this->discounts = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Getter function for the id field which is an identifier proerty. This means
     * doctrine association can be declared over this field.
     * The id field has no setter function, because the value is generated automatically.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter function for the key column property.
     * Column property for the database field groupkey
     *
     * @param string $key
     *
     * @return Group
     */
    public function setKey($key)
    {
        $this->key = $key;

        return $this;
    }

    /**
     * Getter function for the key field which is an identifier property. This means
     * doctrine association can be declared over this field.
     * Column property for the database field groupkey
     *
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * Setter function for the description property which is
     * a column property for the database field description.
     *
     * @param string $name
     *
     * @return Group
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Getter function for the description property which is
     * a column property for the database field description.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Setter function for the tax property which is
     * a column property for the database field tax.
     *
     * @param bool $tax
     *
     * @return Group
     */
    public function setTax($tax)
    {
        $this->tax = (bool) $tax;

        return $this;
    }

    /**
     * Getter function for the tax property which is
     * a column property for the database field tax.
     *
     * @return bool
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * Setter function for the taxinput property which is
     * a column property for the database field taxinput.
     *
     * @param bool $taxInput
     *
     * @return Group
     */
    public function setTaxInput($taxInput)
    {
        $this->taxInput = (bool) $taxInput;

        return $this;
    }

    /**
     * Getter function for the taxinput property which is
     * a column property for the database field taxinput.
     *
     * @return bool
     */
    public function getTaxInput()
    {
        return $this->taxInput;
    }

    /**
     * Setter function for the mode property which is
     * a column property for the database field mode.
     *
     * @param bool $mode
     *
     * @return Group
     */
    public function setMode($mode)
    {
        $this->mode = $mode;

        return $this;
    }

    /**
     * Getter function for the mode property which is
     * a column property for the database field mode.
     *
     * @return bool
     */
    public function getMode()
    {
        return $this->mode;
    }

    /**
     * Setter function for the discount property which is
     * a column property for the database field discount.
     *
     * @param float $discount
     *
     * @return Group
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;

        return $this;
    }

    /**
     * Getter function for the discount property which is
     * a column property for the database field discount.
     *
     * @return float
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * Setter function for the minimumorder property which is
     * a column property for the database field minimumorder.
     *
     * @param float $minimumOrder
     *
     * @return Group
     */
    public function setMinimumOrder($minimumOrder)
    {
        $this->minimumOrder = $minimumOrder;

        return $this;
    }

    /**
     * Getter function for the minimumorder property which is
     * a column property for the database field minimumorder.
     *
     * @return float
     */
    public function getMinimumOrder()
    {
        return $this->minimumOrder;
    }

    /**
     * Setter function for the minimumordersurcharge property which is
     * a column property for the database field minimumordersurcharge.
     *
     * @param float $minimumOrderSurcharge
     *
     * @return Group
     */
    public function setMinimumOrderSurcharge($minimumOrderSurcharge)
    {
        $this->minimumOrderSurcharge = $minimumOrderSurcharge;

        return $this;
    }

    /**
     * Getter function for the minimumordersurcharge property which is
     * a column property for the database field minimumordersurcharge.
     *
     * @return float
     */
    public function getMinimumOrderSurcharge()
    {
        return $this->minimumOrderSurcharge;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<Discount>
     */
    public function getDiscounts()
    {
        return $this->discounts;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Customer\Discount> $discounts
     */
    public function setDiscounts($discounts)
    {
        $this->discounts = $discounts;
    }

    /**
     * @return CustomerGroup
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Attribute\CustomerGroup>|null $attribute
     *
     * @return Group
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, CustomerGroup::class, 'attribute', 'customerGroup');
    }

    /**
     * @return array
     */
    public function toArray()
    {
        return [
            'id' => $this->getId(),
            'key' => $this->getKey(),
            'tax' => $this->getTax(),
            'mode' => $this->getMode(),
            'minimumorder' => $this->getMinimumOrder(),
            'minimumordersurcharge' => $this->getMinimumOrderSurcharge(),
            'basketdiscount' => $this->getDiscount(),
        ];
    }
}
