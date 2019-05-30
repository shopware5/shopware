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
use Shopware\Components\Model\LazyFetchModelEntity;

/**
 * Shopware Address model represents a mail address.
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_campaigns_mailaddresses")
 */
class Address extends LazyFetchModelEntity
{
    /**
     * OWNING SIDE
     * The customer property is the owning side of the association between customer and newsletter address.
     * The association is joined over the newsletter mail address and the customer mail address
     *
     * @var \Shopware\Models\Customer\Customer
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Customer")
     * @ORM\JoinColumn(name="email", referencedColumnName="email")
     */
    protected $customer;

    /**
     * OWNING SIDE
     * The group property is the owning side of the association between group and newsletter group
     * The association is joined over the address groupId and the group's id
     *
     * @var \Shopware\Models\Newsletter\Group
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Newsletter\Group")
     * @ORM\JoinColumn(name="groupID", referencedColumnName="id")
     */
    protected $newsletterGroup;

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
     * Does this address belong to a customer?
     *
     * @var bool
     *
     * @ORM\Column(name="customer", type="boolean", nullable=false)
     */
    private $isCustomer;

    /**
     * ID of the newsletter-group this mail address belongs to
     *
     * @var int
     *
     * @ORM\Column(name="groupID", type="integer", length=11, nullable=true)
     */
    private $groupId = 0;

    /**
     * The actual email address
     *
     * @var string
     *
     * @ORM\Column(name="email", type="string", length=90, nullable=false)
     */
    private $email;

    /**
     * ID of the last newsletter this user received
     *
     * @var int
     *
     * @ORM\Column(name="lastmailing", type="integer", length=11, nullable=false)
     */
    private $lastNewsletterId = 0;

    /**
     * OWNING SIDE
     * The lastNewsletter property is the owning side of the association between a newsletter and a mail-address
     * The association is joined over the lastNewsletterId and Newsletter.id
     *
     * @var \Shopware\Models\Newsletter\Newsletter
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Newsletter\Newsletter", inversedBy="addresses")
     * @ORM\JoinColumn(name="lastmailing", referencedColumnName="id")
     */
    private $lastNewsletter;

    /**
     * ID of the last mailing this user read
     *
     * @var int
     *
     * @ORM\Column(name="lastread", type="integer", length=11, nullable=false)
     */
    private $lastReadId = 0;

    /**
     * The Double-Opt-In registration date
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="added", type="datetime", nullable=true)
     */
    private $added;

    /**
     * The Double-Opt-In confirmation date
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="double_optin_confirmed", type="datetime", nullable=true)
     */
    private $doubleOptinConfirmed;

    /**
     * Sets the default value for the added column
     */
    public function __construct()
    {
        $this->added = new \DateTime();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param bool $isCustomer
     */
    public function setIsCustomer($isCustomer)
    {
        $this->isCustomer = $isCustomer;
    }

    /**
     * @return bool
     */
    public function getIsCustomer()
    {
        return $this->isCustomer;
    }

    /**
     * @param int $lastNewsletterId
     */
    public function setLastNewsletterId($lastNewsletterId)
    {
        $this->lastNewsletterId = $lastNewsletterId;
    }

    /**
     * @return int
     */
    public function getLastNewsletterId()
    {
        return $this->lastNewsletterId;
    }

    /**
     * @deprecated Use `setLastNewsletterId()` instead
     *
     * @param int $lastMailingId
     */
    public function setLastMailingId($lastMailingId)
    {
        $this->lastNewsletterId = $lastMailingId;
    }

    /**
     * @deprecated Use `getLastNewsletterId()` instead
     *
     * @return int
     */
    public function getLastMailingId()
    {
        return $this->lastNewsletterId;
    }

    /**
     * @param int $lastReadId
     */
    public function setLastReadId($lastReadId)
    {
        $this->lastReadId = $lastReadId;
    }

    /**
     * @return int
     */
    public function getLastReadId()
    {
        return $this->lastReadId;
    }

    /**
     * @param \Shopware\Models\Newsletter\Group $newsletterGroup
     *
     * @return Address
     */
    public function setNewsletterGroup($newsletterGroup)
    {
        $this->newsletterGroup = $newsletterGroup;

        return $this;
    }

    /**
     * @return \Shopware\Models\Newsletter\Group
     */
    public function getNewsletterGroup()
    {
        return $this->newsletterGroup;
    }

    /**
     * @param int $groupId
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;
    }

    /**
     * @return int
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @return \Shopware\Models\Customer\Customer
     */
    public function getCustomer()
    {
        /** @var \Shopware\Models\Customer\Customer $customer */
        $customer = $this->fetchLazy($this->customer, ['email' => $this->email]);

        return $customer;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @param \DateTimeInterface $added
     */
    public function setAdded($added)
    {
        $this->added = $added;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDoubleOptinConfirmed()
    {
        return $this->doubleOptinConfirmed;
    }

    /**
     * @param \DateTimeInterface $doubleOptinConfirmed
     */
    public function setDoubleOptinConfirmed($doubleOptinConfirmed)
    {
        $this->doubleOptinConfirmed = $doubleOptinConfirmed;
    }
}
