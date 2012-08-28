<?php
/**
 * Shopware 4.0
 * Copyright © 2012 shopware AG
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

namespace Shopware\Models\Attribute;

use Doctrine\ORM\Mapping as ORM,
    Shopware\Components\Model\ModelEntity;

/**
 * Shopware\Models\Attribute\Dispatch
 *
 * @ORM\Table(name="s_premium_dispatch_attributes")
 * @ORM\Entity
 */
class Dispatch extends ModelEntity
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
     * @var integer $dispatchId
     *
     * @ORM\Column(name="dispatchID", type="integer", nullable=true)
     */
    private $dispatchId = null;

    /**
     * @var Shopware\Models\Dispatch\Dispatch
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Dispatch\Dispatch", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="dispatchID", referencedColumnName="id")
     * })
     */
    private $dispatch;

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
     * Set dispatch
     *
     * @param Shopware\Models\Dispatch\Dispatch $dispatch
     * @return Dispatch
     */
    public function setDispatch(\Shopware\Models\Dispatch\Dispatch $dispatch = null)
    {
        $this->dispatch = $dispatch;
        return $this;
    }

    /**
     * Get dispatch
     *
     * @return Shopware\Models\Dispatch\Dispatch
     */
    public function getDispatch()
    {
        return $this->dispatch;
    }

    /**
     * Set dispatchId
     *
     * @param integer $dispatchId
     * @return Dispatch
     */
    public function setDispatchId($dispatchId)
    {
        $this->dispatchId = $dispatchId;
        return $this;
    }

    /**
     * Get dispatchId
     *
     * @return integer
     */
    public function getDispatchId()
    {
        return $this->dispatchId;
    }
}
