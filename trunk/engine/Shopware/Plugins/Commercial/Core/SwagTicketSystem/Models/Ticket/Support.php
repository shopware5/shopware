<?php
namespace   Shopware\CustomModels\Ticket;
use         Shopware\Components\Model\ModelEntity,
            Doctrine\Common\Collections\ArrayCollection,
            Doctrine\ORM\Mapping AS ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_ticket_support")
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\HasLifecycleCallbacks
 */
class Support extends ModelEntity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $uniqueId
     *
     * @ORM\Column(name="uniqueID", type="string", nullable=false)
     */
    private $uniqueId;

    /**
     * @var integer $userId
     *
     * @ORM\Column(name="userID", type="integer", nullable=false)
     */
    private $userId = 0;

    /**
     * @var integer $employeeId
     *
     * @ORM\Column(name="employeeID", type="integer", nullable=false)
     */
    private $employeeId = 0;

    /**
     * @var integer $ticketTypeId
     *
     * @ORM\Column(name="ticket_typeID", type="integer", nullable=false)
     */
    private $ticketTypeId;

    /**
     * @var integer $statusId
     *
     * @ORM\Column(name="statusID", type="integer", nullable=false)
     */
    private $statusId = 0;

    /**
     * @var integer $shopId
     *
     * @ORM\Column(name="shop_id", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * @var string $email
     *
     * @ORM\Column(name="email", type="string", nullable=false)
     */
    private $email;

    /**
     * @var string $subject
     *
     * @ORM\Column(name="subject", type="string", nullable=false)
     */
    private $subject;

    /**
     * @var string $message
     *
     * @ORM\Column(name="message", type="string", nullable=false)
     */
    private $message;

    /**
     * @var \DateTime $receipt
     *
     * @ORM\Column(name="receipt", type="datetime", nullable=false)
     */
    private $receipt;

    /**
     * @var \DateTime $lastContact
     *
     * @ORM\Column(name="last_contact", type="datetime", nullable=false)
     */
    private $lastContact;

    /**
     * @var string $additional
     *
     * @ORM\Column(name="additional", type="string", nullable=false)
     */
    private $additional;

    /**
     * @var string $isoCode
     *
     * @ORM\Column(name="isocode", type="string", nullable=false)
     */
    private $isoCode;

    /**
     * Owning Side
     *
     * @var \Shopware\CustomModels\Ticket\Status
     * @ORM\ManyToOne(targetEntity="Status", inversedBy="tickets")
     * @ORM\JoinColumn(name="statusID", referencedColumnName="id")
     */
    private $status;

    /**
     * Owning Side
     *
     * @var \Shopware\CustomModels\Ticket\Type
     * @ORM\ManyToOne(targetEntity="Type", inversedBy="tickets")
     * @ORM\JoinColumn(name="ticket_typeID", referencedColumnName="id")
     */
    private $type;

    /**
     * OWNING SIDE - UNI DIRECTIONAL
     * @var \Shopware\Models\Customer\Customer
     * @ORM\OneToOne(targetEntity="Shopware\Models\Customer\Customer")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    protected $customer;

    /**
     * OWNING SIDE - UNI DIRECTIONAL
     * @var \Shopware\Models\Shop\Shop
     * @ORM\OneToOne(targetEntity="Shopware\Models\Shop\Shop")
     * @ORM\JoinColumn(name="shop_id", referencedColumnName="id")
     */
    protected $shop;

    /**
     * INVERSE SIDE
     *
     * @ORM\OneToMany(targetEntity="Shopware\CustomModels\Ticket\History", mappedBy="ticket", orphanRemoval=true)
     * @var ArrayCollection
     */
    protected $history;


    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->history = new ArrayCollection();
    }

    /**
     * Returns the id
     *
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $additional
     */
    public function setAdditional($additional)
    {
        $this->additional = $additional;
    }

    /**
     * @return string
     */
    public function getAdditional()
    {
        return $this->additional;
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
     * @param int $employeeId
     */
    public function setEmployeeId($employeeId)
    {
        $this->employeeId = $employeeId;
    }

    /**
     * @return int
     */
    public function getEmployeeId()
    {
        return $this->employeeId;
    }

    /**
     * @param \DateTime $lastContact
     */
    public function setLastContact($lastContact)
    {
        if (!$lastContact instanceof \DateTime && strlen($lastContact) > 0) {
            $lastContact = new \DateTime($lastContact);
        }
        $this->lastContact = $lastContact;
    }

    /**
     * @return \DateTime
     */
    public function getLastContact()
    {
        return $this->lastContact;
    }

    /**
     * @param string $message
     */
    public function setMessage($message)
    {
        $this->message = $message;
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * @param \DateTime $receipt
     */
    public function setReceipt($receipt)
    {
        if (!$receipt instanceof \DateTime && strlen($receipt) > 0) {
            $receipt = new \DateTime($receipt);
        }
        $this->receipt = $receipt;
    }

    /**
     * @return \DateTime
     */
    public function getReceipt()
    {
        return $this->receipt;
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
     * @param string $uniqueId
     */
    public function setUniqueId($uniqueId)
    {
        $this->uniqueId = $uniqueId;
    }

    /**
     * @return string
     */
    public function getUniqueId()
    {
        return $this->uniqueId;
    }

    /**
     * @return string
     */
    public function getIsoCode()
    {
        return $this->isoCode;
    }

    /**
     * @param string $isoCode
     */
    public function setIsoCode($isoCode)
    {
        $this->isoCode = $isoCode;
    }

    /**
     * @param \Shopware\CustomModels\Ticket\Status $status
     */
    public function setStatus($status)
    {
        $this->status = $status;
    }

    /**
     * @return \Shopware\CustomModels\Ticket\Status
     */
    public function getStatus()
    {
        return $this->status;
    }

    /**
     * @return \Shopware\CustomModels\Ticket\Type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param \Shopware\CustomModels\Ticket\Type $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @param \Shopware\Models\Customer\Customer $customer
     */
    public function setCustomer($customer)
    {
        $this->customer = $customer;
    }

    /**
     * @return \Shopware\Models\Customer\Customer
     */
    public function getCustomer()
    {
        return $this->customer;
    }

    /**
     * @param \Shopware\Models\Shop\Shop $shop
     */
    public function setShop($shop)
    {
        $this->shop = $shop;
    }

    /**
     * @return \Shopware\Models\Shop\Shop
     */
    public function getShop()
    {
        return $this->shop;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $history
     */
    public function setHistory($history)
    {
        $this->history = $history;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getHistory()
    {
        return $this->history;
    }

}