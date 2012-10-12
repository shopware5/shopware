<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_core_customergroups_attributes")
 */
class CustomerGroup extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $customerGroupId
 *
 * @ORM\Column(name="customerGroupID", type="integer", nullable=true)
 */
 protected $customerGroupId;


/**
 * @var \Shopware\Models\Customer\Group
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Group", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="customerGroupID", referencedColumnName="id")
 * })
 */
private $customerGroup;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getCustomerGroupId()
{
    return $this->customerGroupId;
}
        

public function setCustomerGroupId($customerGroupId)
{
    $this->customerGroupId = $customerGroupId;
    return $this;
}
        

public function getCustomerGroup()
{
    return $this->customerGroup;
}

public function setCustomerGroup($customerGroup)
{
    $this->customerGroup = $customerGroup;
    return $this;
}
        
}