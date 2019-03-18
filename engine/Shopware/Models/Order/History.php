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
 * Shopware order history model represents the status history for a single order.
 * If the order or payment status are changed a new entry will be saved in the history.
 * The following data will be saved: The orderID of the changed order, the change date, the previous and current order and payment status,
 * an optional comment which can be set over the backend module and the associated user (optional), if the status
 * has changed from the backend order module.
 *
 * The order history has the following associations:
 * <code>
 *   - Order                    =>  Shopware\Models\Order\Order    [s_order]     bi-directional
 *   - User                     =>  Shopware\Models\User\User      [s_core_auth] uni-directional
 *   - Previous order status    =>  Shopware\Models\Order\Status   [core_states] uni-directional
 *   - Previous payment status  =>  Shopware\Models\Order\Status   [core_states] uni-directional
 *   - Current order status     =>  Shopware\Models\Order\Status   [core_states] uni-directional
 *   - Current payment status   =>  Shopware\Models\Order\Status   [core_states] uni-directional
 * </code>
 *
 * The s_order_history table contains the following indices:
 * <code>
 *   - PRIMARY KEY (`id`),
 *   - KEY `user` (`userID`),
 *   - KEY `order` (`orderID`),
 *   - KEY `current_payment_status` (`payment_status_id`),
 *   - KEY `current_order_status` (`order_status_id`),
 *   - KEY `previous_payment_status` (`previous_payment_status_id`),
 *   - KEY `previous_order_status` (`previous_order_status_id`)
 * </code>
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_order_history")
 */
class History extends ModelEntity
{
    /**
     * Unique identifier field for the history model.
     * This is the primary key field. (strategy="IDENTITY")
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * The $orderId property contains the order identifier for the associated order.
     * Used for the $order association property.
     *
     * @var int
     *
     * @ORM\Column(name="orderID", type="integer", nullable=false)
     */
    private $orderId;

    /**
     * The $userId property contains the unique user id of the user which changed the order status.
     * Used for the $user association property.
     *
     * @var int
     *
     * @ORM\Column(name="userID", type="integer", nullable=true)
     */
    private $userId = null;

    /**
     * The $previousOrderStatusId property contains the id of the previous order status of the order.
     * Used for the $previousOrderStatus association property.
     *
     * @var int
     *
     * @ORM\Column(name="previous_order_status_id", type="integer", nullable=true)
     */
    private $previousOrderStatusId = null;

    /**
     * The $orderStatusId property contains the id of the current order status of the order.
     * Used for the $orderStatus association property.
     *
     * @var int
     *
     * @ORM\Column(name="order_status_id", type="integer", nullable=true)
     */
    private $orderStatusId = null;

    /**
     * The $previousPaymentStatusId property contains the id of the previous payment status of the order.
     * Used for the $previousPaymentStatus association property.
     *
     * @var int
     *
     * @ORM\Column(name="previous_payment_status_id", type="integer", nullable=true)
     */
    private $previousPaymentStatusId = null;

    /**
     * The $paymentStatusId property contains the id of the current payment status of the order.
     * Used for the $paymentStatus association property.
     *
     * @var int
     *
     * @ORM\Column(name="payment_status_id", type="integer", nullable=true)
     */
    private $paymentStatusId = null;

    /**
     * The $comment property allows the user to add a comment for the status change.
     * It will be saved in the s_order_history.comment field.
     *
     * @var string
     *
     * @ORM\Column(name="comment", type="text", nullable=false)
     */
    private $comment = '';

    /**
     * Contains the associated order model.
     * The $order property in this model is the owning side of the association of the order status history and the
     * order model. The $history property of the order model is the inverse side of this association.
     *
     * @var \Shopware\Models\Order\Order
     *
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Order\Order", inversedBy="history")
     * @ORM\JoinColumn(name="orderID", referencedColumnName="id")
     */
    private $order;

    /**
     * Contains the associated user model for the history entry.
     * The $user property in this model is the owning side of the association of the order status history and the
     * user model. This association is an uni-directional association. That means that the user model
     * don't know anything about the order status history.
     *
     * @var \Shopware\Models\User\User
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\User\User")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     */
    private $user;

    /**
     * Contains the associated status model for the previous order status.
     * The $previousOrderStatus property in this model is the owning side of the association of the order status history and the
     * previous order status model. This association is an uni-directional association. That means that the order
     * status model don't know anything about the order status history.
     *
     * @var \Shopware\Models\Order\Status
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Order\Status")
     * @ORM\JoinColumn(name="previous_order_status_id", referencedColumnName="id")
     */
    private $previousOrderStatus;

    /**
     * Contains the associated status model for the current order status.
     * The $orderStatus property in this model is the owning side of the association of the order status history and the
     * current order status model. This association is an uni-directional association. That means that the order
     * status model don't know anything about the order status history.
     *
     * @var \Shopware\Models\Order\Status
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Order\Status")
     * @ORM\JoinColumn(name="order_status_id", referencedColumnName="id")
     */
    private $orderStatus;

    /**
     * Contains the associated status model for the previous payment status.
     * The $previousPaymentStatus property in this model is the owning side of the association of the order status history and the
     * previous payment status model. This association is an uni-directional association. That means that the payment
     * status model don't know anything about the order status history.
     *
     * @var \Shopware\Models\Order\Status
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Order\Status")
     * @ORM\JoinColumn(name="previous_payment_status_id", referencedColumnName="id")
     */
    private $previousPaymentStatus;

    /**
     * Contains the associated status model for the current payment status.
     * The $paymentStatus property in this model is the owning side of the association of the order status history and the
     * current payment status model. This association is an uni-directional association. That means that the payment
     * status model don't know anything about the order status history.
     *
     * @var \Shopware\Models\Order\Status
     *
     * @ORM\OneToOne(targetEntity="\Shopware\Models\Order\Status")
     * @ORM\JoinColumn(name="payment_status_id", referencedColumnName="id")
     */
    private $paymentStatus;

    /**
     * Contains the date when the order status or payment status has been changed.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="change_date", type="datetime", nullable=false)
     */
    private $changeDate;

    /**
     * Getter function for the user property.
     * Unique identifier field for the history model.
     * This is the primary key field. (strategy="IDENTITY")
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Setter function for the user property.
     * Contains the associated user model for the history entry.
     * The $user property in this model is the owning side of the association of the order status history and the
     * user model. This association is an uni-directional association. That means that the user model
     * don't know anything about the order status history.
     *
     * @param \Shopware\Models\User\User $user
     *
     * @return \Shopware\Models\Order\History
     */
    public function setUser($user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Getter function for the user property.
     * Contains the associated user model for the history entry.
     * The $user property in this model is the owning side of the association of the order status history and the
     * user model. This association is an uni-directional association. That means that the user model
     * don't know anything about the order status history.
     *
     * @return \Shopware\Models\User\User
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * Setter function for the user property.
     * The $comment property allows the user to add a comment for the status change.
     * It will be saved in the s_order_history.comment field.
     *
     * @param string $comment
     *
     * @return \Shopware\Models\Order\History
     */
    public function setComment($comment)
    {
        $this->comment = $comment;

        return $this;
    }

    /**
     * Getter function for the comment property.
     * The $comment property allows the user to add a comment for the status change.
     * It will be saved in the s_order_history.comment field.
     *
     * @return string
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * Setter function for the user property.
     * Contains the associated order model.
     * The $order property in this model is the owning side of the association of the order status history and the
     * order model. The $history property of the order model is the inverse side of this association.
     *
     * @param \Shopware\Models\Order\Order $order
     *
     * @return \Shopware\Models\Order\History
     */
    public function setOrder($order)
    {
        $this->order = $order;

        return $this;
    }

    /**
     * Getter function for the user property.
     * Contains the associated order model.
     * The $order property in this model is the owning side of the association of the order status history and the
     * order model. The $history property of the order model is the inverse side of this association.
     *
     * @return \Shopware\Models\Order\Order
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * Setter function for the user property.
     * Contains the associated status model for the current order status.
     * The $orderStatus property in this model is the owning side of the association of the order status history and the
     * current order status model. This association is an uni-directional association. That means that the order
     * status model don't know anything about the order status history.
     *
     * @param \Shopware\Models\Order\Status $orderStatus
     *
     * @return \Shopware\Models\Order\History
     */
    public function setOrderStatus($orderStatus)
    {
        $this->orderStatus = $orderStatus;

        return $this;
    }

    /**
     * Getter function for the user property.
     * Contains the associated status model for the current order status.
     * The $orderStatus property in this model is the owning side of the association of the order status history and the
     * current order status model. This association is an uni-directional association. That means that the order
     * status model don't know anything about the order status history.
     *
     * @return \Shopware\Models\Order\Status
     */
    public function getOrderStatus()
    {
        return $this->orderStatus;
    }

    /**
     * Setter function for the user property.
     * Contains the associated status model for the current payment status.
     * The $paymentStatus property in this model is the owning side of the association of the order status history and the
     * current payment status model. This association is an uni-directional association. That means that the payment
     * status model don't know anything about the order status history.
     *
     * @param \Shopware\Models\Order\Status $paymentStatus
     *
     * @return \Shopware\Models\Order\History
     */
    public function setPaymentStatus($paymentStatus)
    {
        $this->paymentStatus = $paymentStatus;

        return $this;
    }

    /**
     * Getter function for the user property.
     * Contains the associated status model for the current payment status.
     * The $paymentStatus property in this model is the owning side of the association of the order status history and the
     * current payment status model. This association is an uni-directional association. That means that the payment
     * status model don't know anything about the order status history.
     *
     * @return \Shopware\Models\Order\Status
     */
    public function getPaymentStatus()
    {
        return $this->paymentStatus;
    }

    /**
     * Setter function for the user property.
     * Contains the associated status model for the previous order status.
     * The $previousOrderStatus property in this model is the owning side of the association of the order status history and the
     * previous order status model. This association is an uni-directional association. That means that the order
     * status model don't know anything about the order status history.
     *
     * @param \Shopware\Models\Order\Status $previousOrderStatus
     *
     * @return \Shopware\Models\Order\History
     */
    public function setPreviousOrderStatus($previousOrderStatus)
    {
        $this->previousOrderStatus = $previousOrderStatus;

        return $this;
    }

    /**
     * Getter function for the user property.
     * Contains the associated status model for the previous order status.
     * The $previousOrderStatus property in this model is the owning side of the association of the order status history and the
     * previous order status model. This association is an uni-directional association. That means that the order
     * status model don't know anything about the order status history.
     *
     * @return \Shopware\Models\Order\Status
     */
    public function getPreviousOrderStatus()
    {
        return $this->previousOrderStatus;
    }

    /**
     * Setter function for the previousPaymentStatus property.
     * Contains the associated status model for the previous payment status.
     * The $previousPaymentStatus property in this model is the owning side of the association of the order status history and the
     * previous payment status model. This association is an uni-directional association. That means that the payment
     * status model don't know anything about the order status history.
     *
     * @param \Shopware\Models\Order\Status $previousPaymentStatus
     *
     * @return \Shopware\Models\Order\History
     */
    public function setPreviousPaymentStatus($previousPaymentStatus)
    {
        $this->previousPaymentStatus = $previousPaymentStatus;

        return $this;
    }

    /**
     * Getter function for the previousPaymentStatus property.
     * Contains the associated status model for the previous payment status.
     * The $previousPaymentStatus property in this model is the owning side of the association of the order status history and the
     * previous payment status model. This association is an uni-directional association. That means that the payment
     * status model don't know anything about the order status history.
     *
     * @return \Shopware\Models\Order\Status
     */
    public function getPreviousPaymentStatus()
    {
        return $this->previousPaymentStatus;
    }

    /**
     * Getter function for the changeDate property.
     * Contains the date when the order status or payment status has been changed.
     *
     * @return \DateTimeInterface
     */
    public function getChangeDate()
    {
        return $this->changeDate;
    }

    /**
     * Setter function for the changeDate property.
     * Contains the date when the order status or payment status has been changed.
     *
     * @param \DateTimeInterface $changeDate
     */
    public function setChangeDate($changeDate)
    {
        $this->changeDate = $changeDate;
    }
}
