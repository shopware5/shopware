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
 * Shopware\Models\Attribute\CustomerBilling
 *
 * @ORM\Table(name="s_user_billingaddress_attributes")
 * @ORM\Entity
 */
class CustomerBilling extends ModelEntity
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
     * @var string $text1
     *
     * @ORM\Column(name="text1", type="string", length=255, nullable=true)
     */
    private $text1 = null;

    /**
     * @var string $text2
     *
     * @ORM\Column(name="text2", type="string", length=255, nullable=true)
     */
    private $text2 = null;

    /**
     * @var string $text3
     *
     * @ORM\Column(name="text3", type="string", length=255, nullable=true)
     */
    private $text3 = null;

    /**
     * @var string $text4
     *
     * @ORM\Column(name="text4", type="string", length=255, nullable=true)
     */
    private $text4 = null;

    /**
     * @var string $text5
     *
     * @ORM\Column(name="text5", type="string", length=255, nullable=true)
     */
    private $text5 = null;

    /**
     * @var string $text6
     *
     * @ORM\Column(name="text6", type="string", length=255, nullable=true)
     */
    private $text6 = null;

    /**
     * @var integer $customerBillingId
     *
     * @ORM\Column(name="billingID", type="integer", nullable=true)
     */
    private $customerBillingId = null;

    /**
     * @var \Shopware\Models\Customer\Billing
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Billing", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="billingID", referencedColumnName="id")
     * })
     */
    private $customerBilling;

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
     * Set text1
     *
     * @param string $text1
     * @return CustomerBilling
     */
    public function setText1($text1)
    {
        $this->text1 = $text1;
        return $this;
    }

    /**
     * Get text1
     *
     * @return string
     */
    public function getText1()
    {
        return $this->text1;
    }

    /**
     * Set text2
     *
     * @param string $text2
     * @return CustomerBilling
     */
    public function setText2($text2)
    {
        $this->text2 = $text2;
        return $this;
    }

    /**
     * Get text2
     *
     * @return string
     */
    public function getText2()
    {
        return $this->text2;
    }

    /**
     * Set text3
     *
     * @param string $text3
     * @return CustomerBilling
     */
    public function setText3($text3)
    {
        $this->text3 = $text3;
        return $this;
    }

    /**
     * Get text3
     *
     * @return string
     */
    public function getText3()
    {
        return $this->text3;
    }

    /**
     * Set text4
     *
     * @param string $text4
     * @return CustomerBilling
     */
    public function setText4($text4)
    {
        $this->text4 = $text4;
        return $this;
    }

    /**
     * Get text4
     *
     * @return string
     */
    public function getText4()
    {
        return $this->text4;
    }

    /**
     * Set text5
     *
     * @param string $text5
     * @return CustomerBilling
     */
    public function setText5($text5)
    {
        $this->text5 = $text5;
        return $this;
    }

    /**
     * Get text5
     *
     * @return string
     */
    public function getText5()
    {
        return $this->text5;
    }

    /**
     * Set text6
     *
     * @param string $text6
     * @return CustomerBilling
     */
    public function setText6($text6)
    {
        $this->text6 = $text6;
        return $this;
    }

    /**
     * Get text6
     *
     * @return string
     */
    public function getText6()
    {
        return $this->text6;
    }

    /**
     * Set customerBilling
     *
     * @param Shopware\Models\Customer\Billing $customerBilling
     * @return CustomerBilling
     */
    public function setCustomerBilling(\Shopware\Models\Customer\Billing $customerBilling = null)
    {
        $this->customerBilling = $customerBilling;
        return $this;
    }

    /**
     * Get customerBilling
     *
     * @return Shopware\Models\Customer\Billing
     */
    public function getCustomerBilling()
    {
        return $this->customerBilling;
    }

    /**
     * Set customerBillingId
     *
     * @param integer $customerBillingId
     * @return CustomerBilling
     */
    public function setCustomerBillingId($customerBillingId)
    {
        $this->customerBillingId = $customerBillingId;
        return $this;
    }

    /**
     * Get customerBillingId
     *
     * @return integer
     */
    public function getCustomerBillingId()
    {
        return $this->customerBillingId;
    }
}
