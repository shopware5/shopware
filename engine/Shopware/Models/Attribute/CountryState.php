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
 * Shopware\Models\Attribute\CountryState
 *
 * @ORM\Table(name="s_core_countries_states_attributes")
 * @ORM\Entity
 */
class CountryState extends ModelEntity
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
     * @var integer $countryStateId
     *
     * @ORM\Column(name="stateID", type="integer", nullable=true)
     */
    private $countryStateId = null;

    /**
     * @var Shopware\Models\Country\State
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Country\State", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="stateID", referencedColumnName="id")
     * })
     */
    private $countryState;

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
     * Set countryState
     *
     * @param Shopware\Models\Country\State $countryState
     * @return CountryState
     */
    public function setCountryState(\Shopware\Models\Country\State $countryState = null)
    {
        $this->countryState = $countryState;
        return $this;
    }

    /**
     * Get countryState
     *
     * @return Shopware\Models\Country\State
     */
    public function getCountryState()
    {
        return $this->countryState;
    }

    /**
     * Set countryStateId
     *
     * @param integer $countryStateId
     * @return CountryState
     */
    public function setCountryStateId($countryStateId)
    {
        $this->countryStateId = $countryStateId;
        return $this;
    }

    /**
     * Get countryStateId
     *
     * @return integer
     */
    public function getCountryStateId()
    {
        return $this->countryStateId;
    }
}
