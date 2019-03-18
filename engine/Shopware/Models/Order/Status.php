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

namespace Shopware\Models\Order;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware order status model represents the status of an order (payment or order state).
 *
 * The Shopware order status model represents a row of the s_core_states table.
 * The s_core_states table has the follows indices:
 * <code>
 *  - PRIMARY KEY (`id`)
 * </code>
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_core_states")
 */
class Status extends ModelEntity
{
    /**
     * Consts defining the group
     */
    const GROUP_STATE = 'state';
    const GROUP_PAYMENT = 'payment';

    /**
     * Consts defining order states
     */
    const ORDER_STATE_CANCELLED = -1;
    const ORDER_STATE_OPEN = 0;
    const ORDER_STATE_IN_PROCESS = 1;
    const ORDER_STATE_COMPLETED = 2;
    const ORDER_STATE_PARTIALLY_COMPLETED = 3;
    const ORDER_STATE_CANCELLED_REJECTED = 4;
    const ORDER_STATE_READY_FOR_DELIVERY = 5;
    const ORDER_STATE_PARTIALLY_DELIVERED = 6;
    const ORDER_STATE_COMPLETELY_DELIVERED = 7;
    const ORDER_STATE_CLARIFICATION_REQUIRED = 8;

    /**
     * Consts defining payment states
     */
    const PAYMENT_STATE_PARTIALLY_INVOICED = 9;
    const PAYMENT_STATE_COMPLETELY_INVOICED = 10;
    const PAYMENT_STATE_PARTIALLY_PAID = 11;
    const PAYMENT_STATE_COMPLETELY_PAID = 12;
    const PAYMENT_STATE_1ST_REMINDER = 13;
    const PAYMENT_STATE_2ND_REMINDER = 14;
    const PAYMENT_STATE_3RD_REMINDER = 15;
    const PAYMENT_STATE_ENCASHMENT = 16;
    const PAYMENT_STATE_OPEN = 17;
    const PAYMENT_STATE_RESERVED = 18;
    const PAYMENT_STATE_DELAYED = 19;
    const PAYMENT_STATE_RE_CREDITING = 20;
    const PAYMENT_STATE_REVIEW_NECESSARY = 21;
    const PAYMENT_STATE_NO_CREDIT_APPROVED = 30;
    const PAYMENT_STATE_THE_CREDIT_HAS_BEEN_PRELIMINARILY_ACCEPTED = 31;
    const PAYMENT_STATE_THE_CREDIT_HAS_BEEN_ACCEPTED = 32;
    const PAYMENT_STATE_THE_PAYMENT_HAS_BEEN_ORDERED = 33;
    const PAYMENT_STATE_A_TIME_EXTENSION_HAS_BEEN_REGISTERED = 34;
    const PAYMENT_STATE_THE_PROCESS_HAS_BEEN_CANCELLED = 35;

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\Mail\Mail
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Mail\Mail", mappedBy="status")
     */
    protected $mail;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=50, nullable=false)
     */
    private $name;

    /**
     * @var int
     *
     * @ORM\Column(name="position", type="integer", nullable=false)
     */
    private $position;

    /**
     * @var string
     *
     * @ORM\Column(name="`group`", type="string", length=25, nullable=false)
     */
    private $group;

    /**
     * @var int
     *
     * @ORM\Column(name="mail", type="integer", nullable=false)
     */
    private $sendMail;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId($id)
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @param int $position
     *
     * @return Status
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $group
     *
     * @return Status
     */
    public function setGroup($group)
    {
        $this->group = $group;

        return $this;
    }

    /**
     * @return string
     */
    public function getGroup()
    {
        return $this->group;
    }

    /**
     * @param int $sendMail
     *
     * @return Status
     */
    public function setSendMail($sendMail)
    {
        $this->sendMail = $sendMail;

        return $this;
    }

    /**
     * @return int
     */
    public function getSendMail()
    {
        return $this->sendMail;
    }

    /**
     * @return \Shopware\Models\Mail\Mail
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * @param \Shopware\Models\Mail\Mail|array|null $mail
     *
     * @return Status
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }
}
