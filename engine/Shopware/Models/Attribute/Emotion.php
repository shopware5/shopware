<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_emotion_attributes")
 */
class Emotion extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $emotionId
 *
 * @ORM\Column(name="emotionID", type="integer", nullable=true)
 */
 protected $emotionId;


/**
 * @var \Shopware\Models\Emotion\Emotion
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Emotion\Emotion", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="emotionID", referencedColumnName="id")
 * })
 */
private $emotion;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getEmotionId()
{
    return $this->emotionId;
}
        

public function setEmotionId($emotionId)
{
    $this->emotionId = $emotionId;
    return $this;
}
        

public function getEmotion()
{
    return $this->emotion;
}

public function setEmotion($emotion)
{
    $this->emotion = $emotion;
    return $this;
}
        
}