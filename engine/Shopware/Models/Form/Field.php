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

namespace Shopware\Models\Form;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware field model represents a single form-field
 *
 * Associations:
 * <code>
 *   - Form => Shopware\Models\Form\Form [n:1]   [s_cms_support]
 * </code>
 *
 * Indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - UNIQUE KEY `name` (`name`, `supportID`)
 * </code>
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_cms_support_fields")
 * @ORM\HasLifecycleCallbacks()
 */
class Field extends ModelEntity
{
    /**
     * The associated form
     *
     * @var \Shopware\Models\Form\Form
     *
     * @ORM\ManyToOne(targetEntity="Form", inversedBy="fields")
     * @ORM\JoinColumn(name="supportID", referencedColumnName="id")
     */
    protected $form;

    /**
     * Primary Key - autoincrement value
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Error Message to display
     *
     * @var string
     *
     * @ORM\Column(name="error_msg", type="string", length=255, nullable=false)
     */
    private $errorMsg;

    /**
     * Name of Formfield
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Addition note to display
     *
     * @var string
     *
     * @ORM\Column(name="note", type="string", length=255, nullable=true)
     */
    private $note;

    /**
     * Type of formfield e.G. text / email / radio / textarea
     *
     * @var string
     *
     * @ORM\Column(name="typ", type="string", length=255, nullable=false)
     */
    private $typ;

    /**
     * Whether not this field is required
     *
     * @var int
     *
     * @ORM\Column(name="required", type="integer", nullable=false)
     */
    private $required;

    /**
     * The label to show in forms
     *
     * @var string
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     */
    private $label;

    /**
     * Class of display type. e.G. normal / street;nr
     *
     * @var string
     *
     * @ORM\Column(name="class", type="string", length=255, nullable=false)
     */
    private $class;

    /**
     * Prefilled value of field. Mandatory on Dropdowns.
     *
     * @var string
     *
     * @ORM\Column(name="value", type="string", length=255, nullable=false)
     */
    private $value;

    /**
     * Defines the date and time when the field was created
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="added", type="datetime", nullable=false)
     */
    private $added;

    /**
     * Position of this field in the current form
     *
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="ticket_task", type="string", length=200, nullable=false)
     */
    private $ticketTask = '';

    /**
     * Primary key of associated form
     *
     * @var int
     *
     * @ORM\Column(name="supportID", type="integer", nullable=false)
     */
    private $formId;

    /**
     * Set the associated form
     *
     * @param \Shopware\Models\Form\Form $form
     *
     * @return \Shopware\Models\Form\Field
     */
    public function setForm(Form $form)
    {
        $this->form = $form;

        return $this;
    }

    /**
     * Get the unique identifier
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get id of related form
     *
     * @return int
     */
    public function getFormId()
    {
        return $this->formId;
    }

    /**
     * Set error message
     *
     * @param string $errorMsg
     *
     * @return \Shopware\Models\Form\Field
     */
    public function setErrorMsg($errorMsg)
    {
        $this->errorMsg = $errorMsg;

        return $this;
    }

    /**
     * Get error message
     *
     * @return string
     */
    public function getErrorMsg()
    {
        return $this->errorMsg;
    }

    /**
     * Set name of field
     *
     * @param string $name
     *
     * @return \Shopware\Models\Form\Field
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name of field
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set note of field
     *
     * @param string $note
     *
     * @return \Shopware\Models\Form\Field
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get note of field
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set typ of field
     *
     * @param string $typ
     *
     * @return \Shopware\Models\Form\Field
     */
    public function setTyp($typ)
    {
        $this->typ = $typ;

        return $this;
    }

    /**
     * Get typ of field
     *
     * @return string
     */
    public function getTyp()
    {
        return $this->typ;
    }

    /**
     * Set whether or not this field is required
     *
     * @param int $required
     *
     * @return \Shopware\Models\Form\Field
     */
    public function setRequired($required)
    {
        $this->required = $required;

        return $this;
    }

    /**
     * Get whether or not this field is required
     *
     * @return int
     */
    public function getRequired()
    {
        return $this->required;
    }

    /**
     * Set label of field
     *
     * @param string $label
     *
     * @return \Shopware\Models\Form\Field
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Get label of field
     *
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set class of field
     *
     * @param string $class
     *
     * @return \Shopware\Models\Form\Field
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * Get class of field
     *
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * Set value of field
     *
     * @param string $value
     *
     * @return \Shopware\Models\Form\Field
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * Get value of field
     *
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set position of field
     *
     * @param int $position
     *
     * @return \Shopware\Models\Form\Field
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Get position of field
     *
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * Sets added on pre persist
     *
     * @ORM\PrePersist()
     */
    public function onPrePersist()
    {
        $this->added = new \DateTime('now');
    }

    /**
     * @param string $ticketTask
     *
     * @return \Shopware\Models\Form\Field
     */
    public function setTicketTask($ticketTask)
    {
        $this->ticketTask = $ticketTask;

        return $this;
    }

    /**
     * Get Ticket Task
     *
     * @return string
     */
    public function getTicketTask()
    {
        return $this->ticketTask;
    }
}
