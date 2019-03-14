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

namespace Shopware\Models\Config;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Table(name="s_core_config_element_translations")
 * @ORM\Entity()
 */
class ElementTranslation extends ModelEntity
{
    /**
     * @var \Shopware\Models\Config\Element
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Config\Element", inversedBy="translations")
     * @ORM\JoinColumn(name="element_id", referencedColumnName="id")
     */
    protected $element;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Shop\Locale
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Shop\Locale")
     * @ORM\JoinColumn(name="locale_id", referencedColumnName="id")
     */
    protected $locale;

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
     * @ORM\Column(name="description", type="string", nullable=true)
     */
    private $description = null;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", nullable=true)
     */
    private $label = null;

    /**
     * @var int
     *
     * @ORM\Column(name="element_id", type="integer", nullable=false)
     */
    private $elementId;

    /**
     * @var int
     *
     * @ORM\Column(name="locale_id", type="integer", nullable=false)
     */
    private $localeId;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $description
     *
     * @return ElementTranslation
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $label
     *
     * @return ElementTranslation
     */
    public function setLabel($label)
    {
        $this->label = $label;

        return $this;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @return \Shopware\Models\Config\Element
     */
    public function getElement()
    {
        return $this->element;
    }

    /**
     * @param \Shopware\Models\Config\Element $element
     *
     * @return ElementTranslation
     */
    public function setElement($element)
    {
        $this->element = $element;

        return $this;
    }

    /**
     * @param \Shopware\Models\Shop\Locale $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return \Shopware\Models\Shop\Locale
     */
    public function getLocale()
    {
        return $this->locale;
    }
}
