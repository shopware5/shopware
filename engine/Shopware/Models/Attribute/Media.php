<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_media_attributes")
 */
class Media extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $mediaId
 *
 * @ORM\Column(name="mediaID", type="integer", nullable=true)
 */
 protected $mediaId;


/**
 * @var \Shopware\Models\Media\Media
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Media\Media", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="mediaID", referencedColumnName="id")
 * })
 */
private $media;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getMediaId()
{
    return $this->mediaId;
}
        

public function setMediaId($mediaId)
{
    $this->mediaId = $mediaId;
    return $this;
}
        

public function getMedia()
{
    return $this->media;
}

public function setMedia($media)
{
    $this->media = $media;
    return $this;
}
        
}