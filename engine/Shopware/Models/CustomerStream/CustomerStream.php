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

namespace Shopware\Models\CustomerStream;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Table(name="s_customer_streams")
 * @ORM\Entity
 */
class CustomerStream extends ModelEntity
{
    const TYPE_DYNAMIC = 'dynamic';
    const TYPE_STATIC = 'static';

    /**
     * INVERSE SIDE
     *
     * @var \Shopware\Models\Attribute\CustomerStream
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\CustomerStream", mappedBy="customerStream", orphanRemoval=true, cascade={"persist"})
     */
    protected $attribute;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * @var array
     * @ORM\Column(name="conditions", type="string", nullable=true)
     */
    private $conditions;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", nullable=true)
     */
    private $type = self::TYPE_DYNAMIC;

    /**
     * @var \DateTime
     * @ORM\Column(name="freeze_up", type="date", nullable=true)
     */
    private $freezeUp;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param $name string
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return array
     */
    public function getConditions()
    {
        return $this->conditions;
    }

    /**
     * @param array $conditions
     */
    public function setConditions($conditions)
    {
        $this->conditions = $conditions;
    }

    /**
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return \DateTime
     */
    public function getFreezeUp()
    {
        return $this->freezeUp;
    }

    public function setFreezeUp($freezeUp)
    {
        if (is_string($freezeUp)) {
            $freezeUp = new \DateTime($freezeUp);
        }
        $this->freezeUp = $freezeUp;
    }
}
