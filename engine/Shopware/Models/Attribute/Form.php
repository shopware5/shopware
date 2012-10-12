<?php

namespace Shopware\Models\Attribute;
use Shopware\Components\Model\ModelEntity,
    Doctrine\ORM\Mapping AS ORM,
    Symfony\Component\Validator\Constraints as Assert,
    Doctrine\Common\Collections\ArrayCollection;
/**
 * @ORM\Entity
 * @ORM\Table(name="s_cms_support_attributes")
 */
class Form extends ModelEntity
{

/**
 * @var integer $id
 * @ORM\Id
 * @ORM\GeneratedValue(strategy="IDENTITY")
 * @ORM\Column(name="id", type="integer", nullable=false)
 */
 protected $id;


/**
 * @var integer $formId
 *
 * @ORM\Column(name="cmsSupportID", type="integer", nullable=true)
 */
 protected $formId;


/**
 * @var \Shopware\Models\Form\Form
 *
 * @ORM\OneToOne(targetEntity="Shopware\Models\Form\Form", inversedBy="attribute")
 * @ORM\JoinColumns({
 *   @ORM\JoinColumn(name="cmsSupportID", referencedColumnName="id")
 * })
 */
private $form;
        

public function getId()
{
    return $this->id;
}
        

public function setId($id)
{
    $this->id = $id;
    return $this;
}
        

public function getFormId()
{
    return $this->formId;
}
        

public function setFormId($formId)
{
    $this->formId = $formId;
    return $this;
}
        

public function getForm()
{
    return $this->form;
}

public function setForm($form)
{
    $this->form = $form;
    return $this;
}
        
}