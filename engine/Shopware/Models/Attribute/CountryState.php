<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_core_countries_states_attributes")
 */
class CountryState extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $countryStateId
 *
 * @ORM\Column(name="stateID", type="integer", nullable=true)
 */
 protected $countryStateId;


/**
 * @var \Shopware\Models\Country\State
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Country\State", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="stateID", referencedColumnName="id")
 * })
 */
private $countryState;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getCountryStateId()
{
    return $this->countryStateId;
}
        

public function setCountryStateId($countryStateId)
{
    $this->countryStateId = $countryStateId;
    return $this;
}
        

public function getCountryState()
{
    return $this->countryState;
}

public function setCountryState($countryState)
{
    $this->countryState = $countryState;
    return $this;
}
        
}