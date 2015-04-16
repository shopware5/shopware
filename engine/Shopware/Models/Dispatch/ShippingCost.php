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

namespace   Shopware\Models\Dispatch;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * The Shopware Model represents the shipping costs matrix.
 * <br>
 * Shipping costs are represented as a scale. Where more items or weight can cause more shipping costs
 *
 * Relations and Associations
 * <code>
 *   - dispatchId =>  Shopware\Models\Dispatch\Dispatch  [n:1] [s_core_dispatch]
 * </code>
 * The s_media_album table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 * </code>
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_premium_shippingcosts")
 * @ORM\HasLifecycleCallbacks
 */
class ShippingCost extends ModelEntity
{
    /**
     * Autoincrement ID
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Start price.
     *
     * @var float $from
     *
     * @ORM\Column(name="`from`", type="decimal", nullable=false)
     */
    private $from;

    /**
     * Price for this entry
     *
     * @var float $value
     *
     * @ORM\Column(name="value", type="decimal", nullable=false)
     */
    private $value;

    /**
     * Multiplicator for this entry
     *
     * @var float $factor
     *
     * @ORM\Column(name="factor", type="decimal", nullable=false)
     */
    private $factor;

    /**
     * Connected dispatch
     *
     * @var integer $dispatchId
     *
     * @ORM\Column(name="dispatchID", type="integer", nullable=false)
     */
    private $dispatchId;

    /**
     * OWNING SIDE
     * @var \Shopware\Models\Dispatch\Dispatch $dispatch
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Dispatch\Dispatch", inversedBy="costsMatrix", cascade={"persist"})
     * @ORM\JoinColumn(name="dispatchID", referencedColumnName="id")
     */
    protected $dispatch;

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set from
     *
     * @param float $from
     * @return ShippingCost
     */
    public function setFrom($from)
    {
        $this->from = $from;
        return $this;
    }

    /**
     * Get from
     *
     * @return float
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * Set value
     *
     * @param float $value
     * @return ShippingCost
     */
    public function setValue($value)
    {
        $this->value = $value;
        return $this;
    }

    /**
     * Get value
     *
     * @return float
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * Set factor
     *
     * @param float $factor
     * @return ShippingCost
     */
    public function setFactor($factor)
    {
        $this->factor = $factor;
        return $this;
    }

    /**
     * Get factor
     *
     * @return float
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * OWNING SIDE
     * of the association between costsMatrix and dispatch
     * @return \Shopware\Models\Dispatch\Dispatch
     */
    public function getDispatch()
    {
        return $this->dispatch;
    }

    /**
     * @param \Shopware\Models\Dispatch\Dispatch|array|null $dispatch
     * @return \Shopware\Components\Model\ModelEntity
     */
    public function setDispatch($dispatch)
    {
        $this->dispatch = $dispatch;
    }
}
