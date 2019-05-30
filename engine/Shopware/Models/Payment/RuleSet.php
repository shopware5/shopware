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

namespace Shopware\Models\Payment;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware ruleSets-model represents a single ruleSet.
 *
 * @ORM\Table(name="s_core_rulesets")
 * @ORM\Entity()
 */
class RuleSet extends ModelEntity
{
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
     * @ORM\Column(name="paymentID", type="integer", nullable=false)
     */
    private $paymentId;

    /**
     * @var string
     *
     * @ORM\Column(name="rule1", type="string", length=255, nullable=false)
     */
    private $rule1;

    /**
     * @var string
     *
     * @ORM\Column(name="value1", type="string", length=255, nullable=false)
     */
    private $value1;

    /**
     * @var string
     *
     * @ORM\Column(name="rule2", type="string", length=255, nullable=false)
     */
    private $rule2;

    /**
     * @var string
     *
     * @ORM\Column(name="value2", type="string", length=255, nullable=false)
     */
    private $value2;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Payment\Payment>
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Payment\Payment", inversedBy="ruleSets")
     * @ORM\JoinColumn(name="paymentID", referencedColumnName="id")
     */
    private $payment;

    /**
     * Gets the id of the ruleSet.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Sets the paymentId of a ruleSet for the mapping to s_core_paymentmeans.
     *
     * @param int $paymentId
     *
     * @return RuleSet
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;

        return $this;
    }

    /**
     * Gets the paymentId of a ruleSet, which is the id of the corresponding payment from s_core_paymentmeans.
     *
     * @return int
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }

    /**
     * Sets the first rule.
     *
     * @param string $rule1
     *
     * @return RuleSet
     */
    public function setRule1($rule1)
    {
        $this->rule1 = $rule1;

        return $this;
    }

    /**
     * Gets the first rule.
     *
     * @return string
     */
    public function getRule1()
    {
        return $this->rule1;
    }

    /**
     * Sets the value for the first rule.
     *
     * @param string $value1
     *
     * @return RuleSet
     */
    public function setValue1($value1)
    {
        $this->value1 = $value1;

        return $this;
    }

    /**
     * Gets the value for the first rule.
     *
     * @return string
     */
    public function getValue1()
    {
        return $this->value1;
    }

    /**
     * Sets the second rule.
     *
     * @param string $rule2
     *
     * @return RuleSet
     */
    public function setRule2($rule2)
    {
        $this->rule2 = $rule2;

        return $this;
    }

    /**
     * Gets the second rule.
     *
     * @return string
     */
    public function getRule2()
    {
        return $this->rule2;
    }

    /**
     * Sets the value of the second rule.
     *
     * @param string $value2
     *
     * @return RuleSet
     */
    public function setValue2($value2)
    {
        $this->value2 = $value2;

        return $this;
    }

    /**
     * Gets the value of the second rule.
     *
     * @return string
     */
    public function getValue2()
    {
        return $this->value2;
    }

    /**
     * Gets the corresponding payment-model.
     *
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Payment\Payment>
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Sets the payment-model.
     *
     * @param \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Payment\Payment> $payment
     *
     * @return \Shopware\Models\Payment\RuleSet
     */
    public function setPayment($payment)
    {
        $this->payment = $payment;

        return $this;
    }
}
