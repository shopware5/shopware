<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Models\Form;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Attribute\Form as FormAttribute;

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
 * @license    http://shopware.de/license
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_cms_support")
 * @ORM\HasLifecycleCallbacks()
 */
class Form extends ModelEntity
{
    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<Field>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Form\Field", mappedBy="form", orphanRemoval=true, cascade={"persist"})
     */
    protected $fields;

    /**
     * INVERSE SIDE
     *
     * @var FormAttribute|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\Form", mappedBy="form", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = true;

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
     * @var string|null
     *
     * @ORM\Column(name="meta_title", type="string", length=255, nullable=true)
     */
    private $metaTitle = '';

    /**
     * @var string|null
     *
     * @ORM\Column(name="meta_keywords", type="text", nullable=true)
     */
    private $metaKeywords = '';

    /**
     * @var string|null
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

    public function __construct()
    {
        $this->fields = new ArrayCollection();
    }

    /**
     * Returns a clone of this form incl. it's fields
     *
     * @return Form
     */
    public function getClone()
    {
        $clonedForm = clone $this;

        foreach ($this->getFields() as $field) {
            $clonedField = clone $field;
            $clonedForm->fields->add($clonedField);

            // Update owning side
            $clonedField->setForm($clonedForm);
        }

        return $clonedForm;
    }

    /**
     * @return ArrayCollection<Field>
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * @param Field[]|null $fields
     *
     * @return Form
     */
    public function setFields($fields)
    {
        return $this->setOneToMany($fields, Field::class, 'fields', 'form');
    }

    /**
     * Adds a field
     *
     * @return Form
     */
    public function addField(Field $field)
    {
        $this->fields->add($field);

        // Update owning side
        $field->setForm($this);

        return $this;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
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
     * @param string $name
     *
     * @return Form
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @param string $text
     *
     * @return Form
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * @param string $email
     *
     * @return Form
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param string $emailTemplate
     *
     * @return Form
     */
    public function setEmailTemplate($emailTemplate)
    {
        $this->emailTemplate = $emailTemplate;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailTemplate()
    {
        return $this->emailTemplate;
    }

    /**
     * @param string $emailSubject
     *
     * @return Form
     */
    public function setEmailSubject($emailSubject)
    {
        $this->emailSubject = $emailSubject;

        return $this;
    }

    /**
     * @return string
     */
    public function getEmailSubject()
    {
        return $this->emailSubject;
    }

    /**
     * @param string $text2
     *
     * @return Form
     */
    public function setText2($text2)
    {
        $this->text2 = $text2;

        return $this;
    }

    /**
     * @return string
     */
    public function getText2()
    {
        return $this->text2;
    }

    /**
     * @param int $ticketTypeid
     *
     * @return Form
     */
    public function setTicketTypeid($ticketTypeid)
    {
        $this->ticketTypeid = $ticketTypeid;

        return $this;
    }

    /**
     * @return int
     */
    public function getTicketTypeid()
    {
        return $this->ticketTypeid;
    }

    /**
     * @param string $isocode
     *
     * @return Form
     */
    public function setIsocode($isocode)
    {
        $this->isocode = $isocode;

        return $this;
    }

    /**
     * @return string
     */
    public function getIsocode()
    {
        return $this->isocode;
    }

    /**
     * @param string|null $metaTitle
     */
    public function setMetaTitle($metaTitle)
    {
        $this->metaTitle = $metaTitle;
    }

    /**
     * @return string|null
     */
    public function getMetaTitle()
    {
        return $this->metaTitle;
    }

    /**
     * @param string|null $metaDescription
     */
    public function setMetaDescription($metaDescription)
    {
        $this->metaDescription = $metaDescription;
    }

    /**
     * @return string|null
     */
    public function getMetaDescription()
    {
        return $this->metaDescription;
    }

    /**
     * @param string|null $metaKeywords
     */
    public function setMetaKeywords($metaKeywords)
    {
        $this->metaKeywords = $metaKeywords;
    }

    /**
     * @return string|null
     */
    public function getMetaKeywords()
    {
        return $this->metaKeywords;
    }

    /**
     * @return FormAttribute|null
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param FormAttribute|array|null $attribute
     *
     * @return Form
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, FormAttribute::class, 'attribute', 'form');
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
