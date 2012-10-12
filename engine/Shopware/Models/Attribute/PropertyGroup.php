<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_filter_attributes")
 */
class PropertyGroup extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $propertyGroupId
 *
 * @ORM\Column(name="filterID", type="integer", nullable=true)
 */
 protected $propertyGroupId;


/**
 * @var \Shopware\Models\Property\Group
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Property\Group", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="filterID", referencedColumnName="id")
 * })
 */
private $propertyGroup;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getPropertyGroupId()
{
    return $this->propertyGroupId;
}
        

public function setPropertyGroupId($propertyGroupId)
{
    $this->propertyGroupId = $propertyGroupId;
    return $this;
}
        

public function getPropertyGroup()
{
    return $this->propertyGroup;
}

public function setPropertyGroup($propertyGroup)
{
    $this->propertyGroup = $propertyGroup;
    return $this;
}
        
}