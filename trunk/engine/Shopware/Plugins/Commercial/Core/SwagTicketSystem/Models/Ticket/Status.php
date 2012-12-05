<?php
namespace   Shopware\CustomModels\Ticket;
use         Shopware\Components\Model\ModelEntity,
            Doctrine\ORM\Mapping AS ORM;

/**
 * History Model represent the s_ticket_support_status table
 *
 * @ORM\Entity
 * @ORM\Table(name="s_ticket_support_status")
 */
class Status extends ModelEntity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var string $responsible
     *
     * @ORM\Column(name="responsible", type="integer", nullable=false)
     */
    private $responsible;

    /**
     * @var string $closed
     *
     * @ORM\Column(name="closed", type="integer", nullable=false)
     */
    private $closed;

    /**
     * @var string $color
     *
     * @ORM\Column(name="color", type="string", nullable=false)
     */
    private $color = 0;


    /**
     * INVERSE SIDE
     *
     * @ORM\OneToMany(targetEntity="Shopware\CustomModels\Ticket\Support", mappedBy="status")
     * @var \Doctrine\Common\Collections\ArrayCollection An array of \Shopware\CustomModels\Ticket\Support Objects
     */
    protected $tickets;


    /**
     * Class constructor.
     */
    public function __construct()
    {
        $this->tickets = new ArrayCollection();
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
     * @param string $closed
     */
    public function setClosed($closed)
    {
        $this->closed = $closed;
    }

    /**
     * @return string
     */
    public function getClosed()
    {
        return $this->closed;
    }

    /**
     * @param string $color
     */
    public function setColor($color)
    {
        $this->color = $color;
    }

    /**
     * @return string
     */
    public function getColor()
    {
        return $this->color;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $responsible
     */
    public function setResponsible($responsible)
    {
        $this->responsible = $responsible;
    }

    /**
     * @return string
     */
    public function getResponsible()
    {
        return $this->responsible;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTickets()
    {
        return $this->tickets;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection|array|null $tickets
     * @return \Shopware\Components\Model\ModelEntity
     */
    public function setTickets($tickets)
    {
        return $this->setOneToMany($tickets, '\Shopware\CustomModels\Ticket\Support', 'tickets', 'status');
    }
}