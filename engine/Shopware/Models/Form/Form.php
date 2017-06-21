<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace   Shopware\Models\Form;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware field model represents a single form
 *
 * Associations:
 * <code>
 *  - Field => Shopware\Models\Form\Field   [1:n]     [cms_support_fields]
 * </code>
 *
 * Indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - UNIQUE KEY `name` (`name`)
 * </code>
 *
 * @category   Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 * @license    http://shopware.de/license
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_cms_support")
 * @ORM\HasLifecycleCallbacks
 */
class Form extends ModelEntity
{
    /**
     * INVERSE SIDE
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Form\Field", mappedBy="form", orphanRemoval=true, cascade={"persist"})
     *
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $fields;

    /**
     * INVERSE SIDE
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Form", mappedBy="form", orphanRemoval=true, cascade={"persist"})
     *
     * @var \Shopware\Models\Attribute\Form
     */
    protected $attribute;
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text", nullable=false)
     */
    private $text = '';

    /**
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=255, nullable=false)
     */
    private $email;

    /**
     * @var string
     *
     * @ORM\Column(name="email_template", type="text", nullable=false)
     */
    private $emailTemplate = '';

    /**
     * @var string
     *
     * @ORM\Column(name="email_subject", type="string", length=255, nullable=false)
     */
    private $emailSubject = '';

    /**
     * @var string
     *
     * @ORM\Column(name="text2", type="text", nullable=false)
     */
    private $text2 = '';

    /**
     * @var int
     *
     * @ORM\Column(name="ticket_typeID", type="integer", nullable=false)
     */
    private $ticketTypeid = 0;

    /**
     * @var string
     *
     * @ORM\Column(name="isocode", type="string", length=3, nullable=false)
     */
    private $isocode = 'de';

    /**
     * @var string
     *
     * @ORM\Column(name="meta_title", type="string", length=255, nullable=true)
     */
    private $metaTitle = '';

    /**
     * @var string
     *
     * @ORM\Column(name="meta_keywords", type="string", length=255, nullable=true)
     */
    private $metaKeywords = '';

    /**
     * @var string
     *
     * @ORM\Column(name="meta_description", type="text", nullable=true)
     */
    private $metaDescription = '';

    /**
     * @var string
     *
     * @ORM\Column(name="shop_ids", type="string", nullable=false)
     */
    private $shopIds;

    /**
     * Constructor of Form
     */
    public function __construct()
    {
        $this->fields = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Returns a clone of this form incl. it's fields
     *
     * @return \Shopware\Models\Form\Form
     */
    public function getClone()
    {
        $clonedForm = clone $this;

        /* @var $field \Shopware\Models\Form\Field */
        foreach ($this->getFields() as $field) {
            $clonedField = clone $field;
            $clonedForm->fields->add($clonedField);

            // update owning side
            $clonedField->setForm($clonedForm);
        }

        return $clonedForm;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $fields
     *
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function setFields($fields)
    {
        return $this->setOneToMany($fields, '\Shopware\Models\Form\Field', 'fields', 'form');
    }

    /**
     * Adds a field
     *
     * @param \Shopware\Models\Form\Field $field
     *
     * @return \Shopware\Models\Form\Form
     */
    public function addField(Field $field)
    {
        $this->fields->add($field);

        // update owning side
        $field->setForm($this);

        return $this;
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get name of form
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     *
     * @param string $name
     *
     * @return \Shopware\Models\Form\Form
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Set text
     *
     * @param string $text
     *
     * @return \Shopware\Models\Form\Form
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set email
     *
     * @param string $email
     *
     * @return \Shopware\Models\Form\Form
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set emailTemplate
     *
     * @param string $emailTemplate
     *
     * @return \Shopware\Models\Form\Form
     */
    public function setEmailTemplate($emailTemplate)
    {
        $this->emailTemplate = $emailTemplate;

        return $this;
    }

    /**
     * Get emailTemplate
     *
     * @return string
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    /**
     * Set emailSubject
     *
     * @param string $emailSubject
     *
     * @return \Shopware\Models\Form\Form
     */
    public function setEmailSubject($emailSubject)
    {
        $this->emailSubject = $emailSubject;

        return $this;
    }

    /**
     * Get emailSubject
     *
     * @return string
     */
    public function getEmailSubject()
    {
        return $this->emailSubject;
    }

    /**
     * Set text2
     *
     * @param string $text2
     *
     * @return \Shopware\Models\Form\Form
     */
    public function setText2($text2)
    {
        $this->text2 = $text2;

        return $this;
    }

    /**
     * Get text2
     *
     * @return string
     */
    public function getText2()
    {
        return $this->text2;
    }

    /**
     * Set ticketTypeid
     *
     * @param int $ticketTypeid
     *
     * @return \Shopware\Models\Form\Form
     */
    public function setTicketTypeid($ticketTypeid)
    {
        $this->ticketTypeid = $ticketTypeid;

        return $this;
    }

    /**
     * Get ticketTypeid
     *
     * @return int
     */
    public function getTicketTypeid()
    {
        return $this->ticketTypeid;
    }

    /**
     * Set isocode
     *
     * @param string $isocode
     *
     * @return \Shopware\Models\Form\Form
     */
    public function setIsocode($isocode)
    {
        $this->isocode = $isocode;

        return $this;
    }

    /**
     * Get isocode
     *
     * @return string
     */
    public function getIsocode()
    {
        return $this->isocode;
    }

    /**
     * @param string $metaTitle
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * @return string
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * @param string $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return string
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param string $metaKeywords
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @return string
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @return \Shopware\Models\Attribute\Form
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\Form|array|null $attribute
     *
     * @return \Shopware\Models\Form\Form
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\Form', 'attribute', 'form');
    }

    /**
     * Returns the unexploded shop ids string (ex: |1|2|)
     *
     * @return string
     */
    public function getShopIds()
    {
        return $this->shopIds;
    }

    /**
     * @param string $shopIds
     */
    public function setShopIds($shopIds)
    {
        $this->shopIds = $shopIds;
    }
}
