<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_emarketing_vouchers_attributes")
 */
class Voucher extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $voucherId
 *
 * @ORM\Column(name="voucherID", type="integer", nullable=true)
 */
 protected $voucherId;


/**
 * @var \Shopware\Models\Voucher\Voucher
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Voucher\Voucher", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="voucherID", referencedColumnName="id")
 * })
 */
private $voucher;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getVoucherId()
{
    return $this->voucherId;
}
        

public function setVoucherId($voucherId)
{
    $this->voucherId = $voucherId;
    return $this;
}
        

public function getVoucher()
{
    return $this->voucher;
}

public function setVoucher($voucher)
{
    $this->voucher = $voucher;
    return $this;
}
        
}