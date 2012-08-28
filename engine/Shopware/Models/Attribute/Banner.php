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
 * Shopware\Models\Attribute\Banner
 *
 * @ORM\Table(name="s_emarketing_banners_attributes")
 * @ORM\Entity
 */
class Banner extends ModelEntity
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
     * @var integer $bannerId
     *
     * @ORM\Column(name="bannerID", type="integer", nullable=true)
     */
    private $bannerId = null;

    /**
     * @var Shopware\Models\Banner\Banner
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Banner\Banner", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="bannerID", referencedColumnName="id")
     * })
     */
    private $banner;

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
     * Set banner
     *
     * @param Shopware\Models\Banner\Banner $banner
     * @return Banner
     */
    public function setBanner(\Shopware\Models\Banner\Banner $banner = null)
    {
        $this->banner = $banner;
        return $this;
    }

    /**
     * Get banner
     *
     * @return Shopware\Models\Banner\Banner
     */
    public function getBanner()
    {
        return $this->banner;
    }

    /**
     * Set bannerId
     *
     * @param integer $bannerId
     * @return Banner
     */
    public function setBannerId($bannerId)
    {
        $this->bannerId = $bannerId;
        return $this;
    }

    /**
     * Get bannerId
     *
     * @return integer
     */
    public function getBannerId()
    {
        return $this->bannerId;
    }
}
