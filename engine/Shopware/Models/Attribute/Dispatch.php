<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_premium_dispatch_attributes")
 */
class Dispatch extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $dispatchId
 *
 * @ORM\Column(name="dispatchID", type="integer", nullable=true)
 */
 protected $dispatchId;


/**
 * @var \Shopware\Models\Dispatch\Dispatch
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Dispatch\Dispatch", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="dispatchID", referencedColumnName="id")
 * })
 */
private $dispatch;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getDispatchId()
{
    return $this->dispatchId;
}
        

public function setDispatchId($dispatchId)
{
    $this->dispatchId = $dispatchId;
    return $this;
}
        

public function getDispatch()
{
    return $this->dispatch;
}

public function setDispatch($dispatch)
{
    $this->dispatch = $dispatch;
    return $this;
}
        
}