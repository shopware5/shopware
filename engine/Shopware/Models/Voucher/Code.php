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

namespace Shopware\Models\Voucher;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Standard Code Model Entity
 *
 * @ORM\Table(name="s_emarketing_voucher_codes")
 * @ORM\Entity()
 */
class Code extends ModelEntity
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
     * @ORM\Column(name="voucherID", type="integer", nullable=false)
     */
    private $voucherId;

    /**
     * @var int
     *
     * @ORM\Column(name="userID", type="integer", nullable=true)
     */
    private $customerId = null;

    /**
     * @var string
     *
     * @ORM\Column(name="code", type="string", length=255, nullable=false)
     */
    private $code;

    /**
     * @var int
     *
     * @ORM\Column(name="cashed", type="integer", nullable=false)
     */
    private $cashed = 0;

    /**
     * @var \Shopware\Models\Voucher\Voucher
     *
     * @ORM\ManyToOne(targetEntity="Voucher", inversedBy="codes")
     * @ORM\JoinColumn(name="voucherID", referencedColumnName="id")
     */
    private $voucher;

    /**
     * @var \Shopware\Models\Voucher\Voucher
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Customer\Customer")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    private $customer;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $voucherId
     *
     * @return Code
     */
    public function setVoucherId($voucherId)
    {
        $this->voucherId = $voucherId;

        return $this;
    }

    /**
     * @return int
     */
    public function getVoucherId()
    {
        return $this->voucherId;
    }

    /**
     * @param int $customerId
     *
     * @return Code
     */
    public function setCustomerId($customerId)
    {
        $this->customerId = $customerId;

        return $this;
    }

    /**
     * @return int
     */
    public function getCustomerId()
    {
        return $this->customerId;
    }

    /**
     * @param string $code
     *
     * @return Code
     */
    public function setCode($code)
    {
        $this->code = $code;

        return $this;
    }

    /**
     * @return string
     */
    public function getCode()
    {
        return $this->code;
    }

    /**
     * @param int $cashed
     *
     * @return Code
     */
    public function setCashed($cashed)
    {
        $this->cashed = $cashed;

        return $this;
    }

    /**
     * @return int
     */
    public function getCashed()
    {
        return $this->cashed;
    }

    /**
     * @return \Shopware\Models\Voucher\Voucher
     */
    public function getVoucher()
    {
        return $this->voucher;
    }

    /**
     * @param \Shopware\Models\Voucher\Voucher $voucher
     */
    public function setVoucher($voucher)
    {
        $this->voucher = $voucher;
    }

    /**
     * @return \Shopware\Models\Voucher\Voucher
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param \Shopware\Models\Voucher\Voucher $user
     */
    public function setCustomer($user)
    {
        $this->customer = $user;
    }
}
