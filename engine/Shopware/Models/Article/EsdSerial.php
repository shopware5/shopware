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

namespace Shopware\Models\Article;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Shopware\Models\Order\Esd as OrderEsd;

/**
 * @ORM\Entity()
 * @ORM\Table(name="s_articles_esd_serials")
 */
class EsdSerial extends ModelEntity
{
    /**
     * OWNING SIDE
     *
     * @var Esd
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Esd", inversedBy="serials")
     * @ORM\JoinColumn(name="esdID", referencedColumnName="id", nullable=false)
     */
    protected $esd;

    /**
     * INVERSE SIDE
     *
     * @var OrderEsd|null
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Order\Esd", mappedBy="serial")
     */
    protected $esdOrder;

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="serialnumber", type="string", length=255, nullable=false)
     */
    private $serialnumber = '';

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return EsdSerial
     */
    public function setEsd(Esd $esd)
    {
        $this->esd = $esd;

        return $this;
    }

    /**
     * @return Esd
     */
    public function getEsd()
    {
        return $this->esd;
    }

    /**
     * @param string $serialnumber
     */
    public function setSerialnumber($serialnumber)
    {
        $this->serialnumber = $serialnumber;
    }

    /**
     * @return string
     */
    public function getSerialnumber()
    {
        return $this->serialnumber;
    }
}
