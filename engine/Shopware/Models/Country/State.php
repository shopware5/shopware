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

namespace   Shopware\Models\Country;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 *
 * @ORM\Entity
 * @ORM\Table(name="s_core_countries_states")
 */
class State extends ModelEntity
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
    private $countryId;

    /**
     * @var integer $position
     *
     * @ORM\Column(name="position", type="integer", nullable=true)
     */
    private $position = null;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    private $name = null;

    /**
     * @var string $shortCode
     *
     * @ORM\Column(name="shortcode", type="string", length=255, nullable=true)
     */
    private $shortCode = null;

    /**
     * @var integer $active
     *
     * @ORM\Column(name="active", type="integer", nullable=true)
     */
    private $active = null;

    /**
     * OWNING SIDE
     * The countries property is the owning side of the association between area and countries.
     * The association is joined over the countryID field and the id field of the country.
     *
     * @var \Shopware\Models\Country\Country $country
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Country\Country", inversedBy="states", cascade={"persist"})
     * @ORM\JoinColumn(name="countryID", referencedColumnName="id")
     */
    protected $country;

    /**
     * INVERSE SIDE
     * @ORM\OneToOne(targetEntity="Shopware\Models\Attribute\CountryState", mappedBy="countryState", orphanRemoval=true, cascade={"persist"})
     * @var \Shopware\Models\Attribute\CountryState
     */
    protected $attribute;

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
     * Set name
     *
     * @param string $name
     * @return Country
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set shortCode
     *
     * @param string $shortCode
     * @return Country
     */
    public function setShortCode($shortCode)
    {
        $this->shortCode = $shortCode;
        return $this;
    }

    /**
     * Get shortCode
     *
     * @return string
     */
    public function getShortCode()
    {
        return $this->shortCode;
    }

    /**
     * Set active
     *
     * @param integer $active
     * @return Country
     */
    public function setActive($active)
    {
        $this->active = $active;
        return $this;
    }

    /**
     * Get active
     *
     * @return integer
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * OWNING SIDE
     * of the association between states and country
     * @return \Shopware\Models\Country\Country
     */
    public function getCountry()
    {
        return $this->country;
    }

    /**
     * @param \Shopware\Models\Country\Country|array|null $country
     * @return \Shopware\Models\Country\State
     */
    public function setCountry($country)
    {
        $this->country = $country;
        return $this;
    }

    /**
     * @return \Shopware\Models\Attribute\CountryState
     */
    public function getAttribute()
    {
        return $this->attribute;
    }

    /**
     * @param \Shopware\Models\Attribute\CountryState|array|null $attribute
     * @return \Shopware\Models\Attribute\CountryState
     */
    public function setAttribute($attribute)
    {
        return $this->setOneToOne($attribute, '\Shopware\Models\Attribute\CountryState', 'attribute', 'countryState');
    }
}
