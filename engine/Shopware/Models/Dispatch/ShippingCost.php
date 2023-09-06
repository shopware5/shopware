<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Models\Dispatch;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

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
 * @ORM\HasLifecycleCallbacks()
 */
class ShippingCost extends ModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var Dispatch
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Dispatch\Dispatch", inversedBy="costsMatrix", cascade={"persist"})
     * @ORM\JoinColumn(name="dispatchID", referencedColumnName="id", nullable=false)
     */
    protected $dispatch;

    /**
     * Autoincrement ID
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Start price.
     *
     * @var string
     *
     * @ORM\Column(name="`from`", type="decimal", precision=10, scale=3, nullable=false)
     */
    private $from;

    /**
     * Price for this entry
     *
     * @var string
     *
     * @ORM\Column(name="value", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $value;

    /**
     * Multiplicand for this entry
     *
     * @var string
     *
     * @ORM\Column(name="factor", type="decimal", precision=10, scale=2, nullable=false)
     */
    private $factor;

    /**
     * Connected dispatch
     *
     * @var int
     *
     * @ORM\Column(name="dispatchID", type="integer", nullable=false)
     */
    private $dispatchId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $from
     *
     * @return ShippingCost
     */
    public function setFrom($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * @return string
     */
    public function getFrom()
    {
        return $this->from;
    }

    /**
     * @param string $value
     *
     * @return ShippingCost
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param string $factor
     *
     * @return ShippingCost
     */
    public function setFactor($factor)
    {
        $this->factor = $factor;

        return $this;
    }

    /**
     * @return string
     */
    public function getFactor()
    {
        return $this->factor;
    }

    /**
     * OWNING SIDE
     * of the association between costsMatrix and dispatch
     *
     * @return Dispatch
     */
    public function getDispatch()
    {
        return $this->dispatch;
    }

    /**
     * @param Dispatch|array $dispatch
     */
    public function setDispatch($dispatch)
    {
        $this->dispatch = $dispatch;
    }
}
