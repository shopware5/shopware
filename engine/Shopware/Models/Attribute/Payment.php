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
 * Shopware\Models\Attribute\Payment
 *
 * @ORM\Table(name="s_core_paymentmeans_attributes")
 * @ORM\Entity
 */
class Payment extends ModelEntity
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
     * @var integer $paymentId
     *
     * @ORM\Column(name="paymentmeanID", type="integer", nullable=true)
     */
    private $paymentId = null;

    /**
     * @var Shopware\Models\Payment\Payment
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Payment\Payment", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="paymentmeanID", referencedColumnName="id")
     * })
     */
    private $payment;

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
     * Set payment
     *
     * @param Shopware\Models\Payment\Payment $payment
     * @return Payment
     */
    public function setPayment(\Shopware\Models\Payment\Payment $payment = null)
    {
        $this->payment = $payment;
        return $this;
    }

    /**
     * Get payment
     *
     * @return Shopware\Models\Payment\Payment
     */
    public function getPayment()
    {
        return $this->payment;
    }

    /**
     * Set paymentId
     *
     * @param integer $paymentId
     * @return Payment
     */
    public function setPaymentId($paymentId)
    {
        $this->paymentId = $paymentId;
        return $this;
    }

    /**
     * Get paymentId
     *
     * @return integer
     */
    public function getPaymentId()
    {
        return $this->paymentId;
    }
}
