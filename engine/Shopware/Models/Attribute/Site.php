<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_cms_static_attributes")
 */
class Site extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $siteId
 *
 * @ORM\Column(name="cmsStaticID", type="integer", nullable=true)
 */
 protected $siteId;


/**
 * @var \Shopware\Models\Site\Site
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Site\Site", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="cmsStaticID", referencedColumnName="id")
 * })
 */
private $site;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getSiteId()
{
    return $this->siteId;
}
        

public function setSiteId($siteId)
{
    $this->siteId = $siteId;
    return $this;
}
        

public function getSite()
{
    return $this->site;
}

public function setSite($site)
{
    $this->site = $site;
    return $this;
}
        
}