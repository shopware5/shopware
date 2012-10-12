<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_core_auth_attributes")
 */
class User extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $userId
 *
 * @ORM\Column(name="authID", type="integer", nullable=true)
 */
 protected $userId;


/**
 * @var \Shopware\Models\User\User
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\User\User", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="authID", referencedColumnName="id")
 * })
 */
private $user;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getUserId()
{
    return $this->userId;
}
        

public function setUserId($userId)
{
    $this->userId = $userId;
    return $this;
}
        

public function getUser()
{
    return $this->user;
}

public function setUser($user)
{
    $this->user = $user;
    return $this;
}
        
}