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
 * Shopware\Models\Attribute\Voucher
 *
 * @ORM\Table(name="s_emarketing_vouchers_attributes")
 * @ORM\Entity
 */
class Voucher extends ModelEntity
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
     * @var integer $voucherId
     *
     * @ORM\Column(name="voucherID", type="integer", nullable=true)
     */
    private $voucherId = null;

    /**
     * @var Shopware\Models\Voucher\Voucher
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Voucher\Voucher", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="voucherID", referencedColumnName="id")
     * })
     */
    private $voucher;

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
     * Set voucher
     *
     * @param Shopware\Models\Voucher\Voucher $voucher
     * @return Voucher
     */
    public function setVoucher(\Shopware\Models\Voucher\Voucher $voucher = null)
    {
        $this->voucher = $voucher;
        return $this;
    }

    /**
     * Get voucher
     *
     * @return Shopware\Models\Voucher\Voucher
     */
    public function getVoucher()
    {
        return $this->voucher;
    }

    /**
     * Set voucherId
     *
     * @param integer $voucherId
     * @return Voucher
     */
    public function setVoucherId($voucherId)
    {
        $this->voucherId = $voucherId;
        return $this;
    }

    /**
     * Get voucherId
     *
     * @return integer
     */
    public function getVoucherId()
    {
        return $this->voucherId;
    }
}
