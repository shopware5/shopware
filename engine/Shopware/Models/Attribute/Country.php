<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_core_countries_attributes")
 */
class Country extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $countryId
 *
 * @ORM\Column(name="countryID", type="integer", nullable=true)
 */
 protected $countryId;


/**
 * @var \Shopware\Models\Country\Country
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Country\Country", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="countryID", referencedColumnName="id")
 * })
 */
private $country;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getCountryId()
{
    return $this->countryId;
}
        

public function setCountryId($countryId)
{
    $this->countryId = $countryId;
    return $this;
}
        

public function getCountry()
{
    return $this->country;
}

public function setCountry($country)
{
    $this->country = $country;
    return $this;
}
        
}