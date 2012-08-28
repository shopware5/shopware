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
 * Shopware\Models\Attribute\Country
 *
 * @ORM\Table(name="s_core_countries_attributes")
 * @ORM\Entity
 */
class Country extends ModelEntity
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
     * @var integer $countryId
     *
     * @ORM\Column(name="countryID", type="integer", nullable=true)
     */
    private $countryId = null;

    /**
     * @var Shopware\Models\Country\Country
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Country\Country", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="countryID", referencedColumnName="id")
     * })
     */
    private $country;

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
     * Set country
     *
     * @param Shopware\Models\Country\Country $country
     * @return Country
     */
    public function setCountry(\Shopware\Models\Country\Country $country = null)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * Get country
     *
     * @return Shopware\Models\Country\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * Set countryId
     *
     * @param integer $countryId
     * @return Country
     */
    public function setCountryId($countryId)
    {
        $this->countryId = $countryId;
        return $this;
    }

    /**
     * Get countryId
     *
     * @return integer
     */
    public function getCountryId()
    {
        return $this->countryId;
    }
}
