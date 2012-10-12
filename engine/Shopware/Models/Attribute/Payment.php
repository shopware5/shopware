<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_core_paymentmeans_attributes")
 */
class Payment extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $paymentId
 *
 * @ORM\Column(name="paymentmeanID", type="integer", nullable=true)
 */
 protected $paymentId;


/**
 * @var \Shopware\Models\Payment\Payment
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Payment\Payment", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="paymentmeanID", referencedColumnName="id")
 * })
 */
private $payment;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getPaymentId()
{
    return $this->paymentId;
}
        

public function setPaymentId($paymentId)
{
    $this->paymentId = $paymentId;
    return $this;
}
        

public function getPayment()
{
    return $this->payment;
}

public function setPayment($payment)
{
    $this->payment = $payment;
    return $this;
}
        
}