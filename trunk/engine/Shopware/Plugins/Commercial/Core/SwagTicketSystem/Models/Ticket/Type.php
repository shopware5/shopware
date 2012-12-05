<?php
namespace   Shopware\CustomModels\Ticket;
use         Shopware\Components\Model\ModelEntity,
            Doctrine\Common\Collections\ArrayCollection,
            Doctrine\ORM\Mapping AS ORM;

/**
 * History Model represent the s_ticket_support_types table
 *
 * @ORM\Entity
 * @ORM\Table(name="s_ticket_support_types")
 */
class Type extends ModelEntity
{
    /**
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string $gridColor
     *
     * @ORM\Column(name="gridColor", type="string", nullable=false)
     */
    private $gridColor;

    /**
     * INVERSE SIDE
     *
     * @ORM\OneToMany(targetEntity="Shopware\CustomModels\Ticket\Support", mappedBy="type")
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
     * @param string $gridColor
     */
    public function setGridColor($gridColor)
    {
        $this->gridColor = $gridColor;
    }

    /**
     * @return string
     */
    public function getGridColor()
    {
        return $this->gridColor;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
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
     * @return \Shopware\Components\Model\ModelEntity
     */
    public function setTickets($tickets)
    {
        return $this->setOneToMany($tickets, '\Shopware\CustomModels\Ticket\Support', 'tickets', 'type');
    }
}