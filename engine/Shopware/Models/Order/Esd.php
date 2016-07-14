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

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * @ORM\Entity
 * @ORM\Table(name="s_order_esd")
 * @ORM\HasLifecycleCallbacks
 */
class Esd extends ModelEntity
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var \DateTime $date
     *
     * @ORM\Column(name="datum", type="datetime", nullable=true)
     */
    private $date = null;

    /**
     * OWNING SIDE
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\EsdSerial", inversedBy="esdOrder")
     * @ORM\JoinColumn(name="serialID", referencedColumnName="id")
     * @var \Shopware\Models\Article\EsdSerial
     */
    protected $serial;

    /**
     * OWNING SIDE
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Order", inversedBy="esd")
     * @ORM\JoinColumn(name="orderID", referencedColumnName="id")
     * @var \Shopware\Models\Order\Order
     */
    protected $order;

    /**
     * OWNING SIDE
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Detail", inversedBy="esd")
     * @ORM\JoinColumn(name="orderdetailsID", referencedColumnName="id")
     * @var \Shopware\Models\Order\Detail
     */
    protected $orderDetail;

    /**
     * @ORM\ManyToOne(targetEntity="\Shopware\Models\Customer\Customer")
     * @ORM\JoinColumn(name="userID", referencedColumnName="id")
     * @var \Shopware\Models\Customer\Customer
     */
    protected $customer;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Shopware\Models\Article\Article $order
     */
    public function setOrder($order)
    {
        $this->order = $order;
    }

    /**
     * @return \Shopware\Models\Article\Article
     */
    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param \Shopware\Models\Article\Detail $orderDetail
     */
    public function setOrderDetail($orderDetail)
    {
        $this->orderDetail = $orderDetail;
    }

    /**
     * @return \Shopware\Models\Article\Detail
     */
    public function getOrderDetail()
    {
        return $this->orderDetail;
    }
}
