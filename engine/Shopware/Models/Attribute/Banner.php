<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_emarketing_banners_attributes")
 */
class Banner extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $bannerId
 *
 * @ORM\Column(name="bannerID", type="integer", nullable=true)
 */
 protected $bannerId;


/**
 * @var \Shopware\Models\Banner\Banner
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Banner\Banner", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="bannerID", referencedColumnName="id")
 * })
 */
private $banner;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getBannerId()
{
    return $this->bannerId;
}
        

public function setBannerId($bannerId)
{
    $this->bannerId = $bannerId;
    return $this;
}
        

public function getBanner()
{
    return $this->banner;
}

public function setBanner($banner)
{
    $this->banner = $banner;
    return $this;
}
        
}