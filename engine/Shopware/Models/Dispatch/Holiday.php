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

namespace Shopware\Models\Dispatch;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * The Shopware Model represents the Holidays stored in the DB.
 * Holiday
 * The Shopware Model represents the Holidays stored in the DB.
 * <br>
 * The Holiday Table contain methods to calculate some holidays.
 *
 * Relations and Associations
 * <code>
 *   - dispatchId =>  Shopware\Models\Dispatch\Dispatch  [n:1] [s_core_dispatch]
 * </code>
 * The table has the follows indices:
 * <code>
 *   - PRIMARY KEY (`dispatchID`,`holidayID`)
 * </code>
 *
 * @ORM\Table(name="s_premium_holidays")
 * @ORM\Entity()
 */
class Holiday extends ModelEntity
{
    /**
     * Autoincrement ID
     *
     * @var int
     *
     * @ORM\Id()
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Name of the Holiday
     *
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * SQL Calculation of this holiday
     *
     * @var string
     *
     * @ORM\Column(name="calculation", type="string", length=255, nullable=false)
     */
    private $calculation;

    /**
     * Next date on which this is due.
     *
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="date", type="date", nullable=false)
     */
    private $date;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     *
     * @return Holiday
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $calculation
     *
     * @return Holiday
     */
    public function setCalculation($calculation)
    {
        $this->calculation = $calculation;

        return $this;
    }

    /**
     * @return string
     */
    public function getCalculation()
    {
        return $this->calculation;
    }

    /**
     * @param \DateTimeInterface $date
     *
     * @return Holiday
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
    }
}
