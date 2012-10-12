<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_core_config_mails_attributes")
 */
class Mail extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $mailId
 *
 * @ORM\Column(name="mailID", type="integer", nullable=true)
 */
 protected $mailId;


/**
 * @var \Shopware\Models\Mail\Mail
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Mail\Mail", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="mailID", referencedColumnName="id")
 * })
 */
private $mail;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getMailId()
{
    return $this->mailId;
}
        

public function setMailId($mailId)
{
    $this->mailId = $mailId;
    return $this;
}
        

public function getMail()
{
    return $this->mail;
}

public function setMail($mail)
{
    $this->mail = $mail;
    return $this;
}
        
}