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

namespace Shopware\Models\Mail;

use DateTimeInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Order\Document\Document;
use Shopware\Models\Order\Order;
use Shopware\Models\Shop\Shop;

/**
 * @ORM\Entity(repositoryClass="LogRepository")
 * @ORM\Table(name="s_mail_log")
 */
class Log extends ModelEntity
{
    /**
     * Primary Key
     *
     * @var int
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="subject", type="text")
     */
    protected $subject;

    /**
     * @var string
     *
     * @ORM\Column(name="sender", type="string")
     */
    protected $sender;

    /**
     * @var Collection<Contact>
     *
     * @ORM\ManyToMany(targetEntity="Contact", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(
     *     name="s_mail_log_recipient",
     *     joinColumns={@ORM\JoinColumn(name="log_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="contact_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $recipients;

    /**
     * @var DateTimeInterface
     *
     * @ORM\Column(name="sent_at", type="datetime")
     */
    protected $sentAt;

    /**
     * @var string
     *
     * @ORM\Column(name="content_html", type="text", nullable=true)
     */
    protected $contentHtml;

    /**
     * @var string
     *
     * @ORM\Column(name="content_text", type="text", nullable=true)
     */
    protected $contentText;

    /**
     * @var Mail
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Mail\Mail")
     */
    protected $type;

    /**
     * @var Order|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Order\Order")
     */
    protected $order;

    /**
     * @var Shop|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Shop")
     */
    protected $shop;

    /**
     * @var Collection<Document>
     *
     * @ORM\ManyToMany(targetEntity="Shopware\Models\Order\Document\Document", cascade={"persist", "remove"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(
     *     name="s_mail_log_document",
     *     joinColumns={@ORM\JoinColumn(name="log_id", referencedColumnName="id", onDelete="CASCADE")},
     *     inverseJoinColumns={@ORM\JoinColumn(name="document_id", referencedColumnName="id", onDelete="CASCADE")}
     * )
     */
    protected $documents;

    public function __construct()
    {
        $this->recipients = new ArrayCollection();
        $this->documents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): void
    {
        $this->id = $id;
    }

    public function getSubject(): ?string
    {
        return $this->subject;
    }

    public function setSubject(?string $subject): void
    {
        $this->subject = $subject ?: '';
    }

    public function getSender(): ?string
    {
        return $this->sender;
    }

    public function setSender(?string $sender): void
    {
        $this->sender = $sender ?: '';
    }

    /**
     * @return Collection<Contact>
     */
    public function getRecipients(): Collection
    {
        return $this->recipients;
    }

    /**
     * @param Collection<Contact> $recipients
     */
    public function setRecipients(Collection $recipients): void
    {
        $this->recipients = $recipients;
    }

    public function addRecipient(Contact $contact): void
    {
        if ($this->recipients->contains($contact)) {
            return;
        }

        $this->recipients->add($contact);
    }

    public function removeRecipient(Contact $contact): void
    {
        if (!$this->recipients->contains($contact)) {
            return;
        }

        $this->recipients->removeElement($contact);
    }

    public function getSentAt(): ?DateTimeInterface
    {
        return $this->sentAt;
    }

    public function setSentAt(DateTimeInterface $sentAt): void
    {
        $this->sentAt = $sentAt;
    }

    public function getContentHtml(): ?string
    {
        return $this->contentHtml;
    }

    public function setContentHtml(?string $contentHtml): void
    {
        $this->contentHtml = $contentHtml ?: '';
    }

    public function getContentText(): ?string
    {
        return $this->contentText;
    }

    public function setContentText(?string $contentText): void
    {
        $this->contentText = $contentText ?: '';
    }

    public function getType(): ?Mail
    {
        return $this->type;
    }

    public function setType(?Mail $type): void
    {
        $this->type = $type;
    }

    public function getOrder(): ?Order
    {
        return $this->order;
    }

    public function setOrder(?Order $order): void
    {
        $this->order = $order;
    }

    public function getShop(): ?Shop
    {
        return $this->shop;
    }

    public function setShop(?Shop $shop): void
    {
        $this->shop = $shop;
    }

    /**
     * @return Collection<Document>
     */
    public function getDocuments(): Collection
    {
        return $this->documents;
    }

    /**
     * @param Collection<Document> $documents
     */
    public function setDocuments(Collection $documents): void
    {
        $this->documents = $documents;
    }

    public function addDocument(Document $document): void
    {
        if ($this->documents->contains($document)) {
            return;
        }

        $this->documents->add($document);
    }

    public function removeDocument(Document $document): void
    {
        if (!$this->documents->contains($document)) {
            return;
        }

        $this->documents->removeElement($document);
    }
}
