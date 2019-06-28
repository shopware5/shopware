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

namespace Shopware\Models\Newsletter;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware newsletter model represents a newsletter.
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_campaigns_mailings")
 */
class Newsletter extends ModelEntity
{
    /**
     * INVERSE SIDE
     *
     * This is the inverse side of the association between newsletters and mail-addresses which have already
     * received the given newsletter.
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Newsletter\Address>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Newsletter\Address", mappedBy="lastNewsletter")
     */
    protected $addresses;

    /**
     * Autoincrement ID
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Date of the newsletter
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="datum", type="date", nullable=true)
     */
    private $date;

    /**
     * @var string
     *
     * @ORM\Column(name="`groups`", type="string", nullable=false)
     */
    private $groups = '';

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="string", length=100, nullable=false)
     */
    private $subject = '';

    /**
     * Mail address of the sender
     *
     * @var string
     *
     * @ORM\Column(name="sendermail", type="string", length=255, nullable=false)
     */
    private $senderMail = '';

    /**
     * Name of the sender
     *
     * @var string
     *
     * @ORM\Column(name="sendername", type="string", length=16777215, nullable=false)
     */
    private $senderName = '';

    /**
     * Is this mail a plaintext mail?
     *
     * @var bool
     *
     * @ORM\Column(name="plaintext", type="boolean", nullable=false)
     */
    private $plaintext = false;

    /**
     * Id of the used template
     * Defaults to one as long as templates are not supported by the newsletter backend module
     *
     * @var int
     *
     * @ORM\Column(name="templateID", type="integer", length=11, nullable=false)
     */
    private $templateId = 1;

    /**
     * Id of the mail's language
     *
     * @var int
     *
     * @ORM\Column(name="languageID", type="integer", nullable=false)
     */
    private $languageId = 0;

    /**
     * Status of the mail
     *
     * @var int
     *
     * @ORM\Column(name="status", type="integer", nullable=false)
     */
    private $status = 0;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="locked", type="datetime", nullable=true)
     */
    private $locked;

    /**
     * Number of recipients
     *
     * @var int
     *
     * @ORM\Column(name="recipients", type="integer", nullable=false)
     */
    private $recipients = 0;

    /**
     * Number of recipients who read the mail
     *
     * @var int
     *
     * @ORM\Column(name="`read`", type="integer", nullable=false)
     */
    private $read = 0;

    /**
     * Number of recipients who clicked a link in the mail
     *
     * @var int
     *
     * @ORM\Column(name="clicked", type="integer", nullable=false)
     */
    private $clicked = 0;

    /**
     * groupkey of the customerGroup this mail was sent to
     *
     * @var string
     *
     * @ORM\Column(name="customergroup", type="string", length=15, nullable=false)
     */
    private $customerGroup = '';

    /**
     * Is this a published mail?
     *
     * @var bool
     *
     * @ORM\Column(name="publish", type="boolean", nullable=false)
     */
    private $publish = false;

    /**
     * Should the mail delivered in future?
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="timed_delivery", type="datetime", nullable=true)
     */
    private $timedDelivery = null;

    /**
     * INVERSE SIDE
     *
     * Inverse side of the mailing-container association
     *
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Newsletter\Container>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Newsletter\Container", mappedBy="newsletter", cascade={"persist", "remove"})
     */
    private $containers;

    public function __construct()
    {
        $this->containers = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * @param \DateTimeInterface $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * @param int $clicked
     */
    public function setClicked($clicked)
    {
        $this->clicked = $clicked;
    }

    /**
     * @return int
     */
    public function getClicked()
    {
        return $this->clicked;
    }

    /**
     * @param string $customerGroup
     */
    public function setCustomerGroup($customerGroup)
    {
        $this->customerGroup = $customerGroup;
    }

    /**
     * @return string
     */
    public function getCustomerGroup()
    {
        return $this->customerGroup;
    }

    /**
     * @param string $groups
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;
    }

    /**
     * @return string
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param int $languageId
     */
    public function setLanguageId($languageId)
    {
        $this->languageId = $languageId;
    }

    /**
     * @return int
     */
    public function getLanguageId()
    {
        return $this->languageId;
    }

    /**
     * @param \DateTimeInterface $locked
     */
    public function setLocked($locked)
    {
        $this->locked = $locked;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getLocked()
    {
        return $this->locked;
    }

    /**
     * @param bool $plaintext
     */
    public function setPlaintext($plaintext)
    {
        $this->plaintext = $plaintext;
    }

    /**
     * @return bool
     */
    public function getPlaintext()
    {
        return $this->plaintext;
    }

    /**
     * @param bool $publish
     */
    public function setPublish($publish)
    {
        $this->publish = $publish;
    }

    /**
     * @return bool
     */
    public function getPublish()
    {
        return $this->publish;
    }

    /**
     * @param int $read
     */
    public function setRead($read)
    {
        $this->read = $read;
    }

    /**
     * @return int
     */
    public function getRead()
    {
        return $this->read;
    }

    /**
     * @param int $recipients
     */
    public function setRecipients($recipients)
    {
        $this->recipients = $recipients;
    }

    /**
     * @return int
     */
    public function getRecipients()
    {
        return $this->recipients;
    }

    /**
     * @param string $senderMail
     */
    public function setSenderMail($senderMail)
    {
        $this->senderMail = $senderMail;
    }

    /**
     * @return string
     */
    public function getSenderMail()
    {
        return $this->senderMail;
    }

    /**
     * @param string $senderName
     */
    public function setSenderName($senderName)
    {
        $this->senderName = $senderName;
    }

    /**
     * @return string
     */
    public function getSenderName()
    {
        return $this->senderName;
    }

    /**
     * @param int $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return int
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @param string $subject
     */
    public function setSubject($subject)
    {
        $this->subject = $subject;
    }

    /**
     * @return string
     */
    public function getSubject()
    {
        return $this->subject;
    }

    /**
     * @param int $templateId
     */
    public function setTemplateId($templateId)
    {
        $this->templateId = $templateId;
    }

    /**
     * @return int
     */
    public function getTemplateId()
    {
        return $this->templateId;
    }

    /**
     * @param \Shopware\Models\Newsletter\Container[] $containers
     *
     * @return Newsletter
     */
    public function setContainers($containers)
    {
        return $this->setOneToMany($containers, \Shopware\Models\Newsletter\Container::class, 'containers', 'newsletter');
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Newsletter\Container>
     */
    public function getContainers()
    {
        return $this->containers;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @deprecated Will be removed without replacement in 6.0
     */
    public function getOrders()
    {
        return null;
    }

    /**
     * @deprecated Will be removed without replacement in 6.0
     */
    public function getAlreadySendTo()
    {
        return null;
    }

    /**
     * @return \DateTimeInterface|null
     */
    public function getTimedDelivery()
    {
        return $this->timedDelivery;
    }

    /**
     * @param \DateTimeInterface $timedDelivery
     */
    public function setTimedDelivery($timedDelivery)
    {
        $this->timedDelivery = $timedDelivery;
    }
}
