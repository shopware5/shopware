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
namespace Shopware\Models\Emotion;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Shopware Emotion Model - Preset translation
 *
 * @category   Shopware
 * @package    Shopware\Models
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity
 * @ORM\Table(name="s_emotion_presets_translation")
 * @ORM\HasLifecycleCallbacks
 */
class PresetTranslation extends ModelEntity
{
    /**
     * Unique identifier field for the shopware emotion translation.
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains the name of the emotion preset.
     *
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     */
    private $label;

    /**
     * Contains the name of the emotion preset.
     *
     * @var string $description
     *
     * @ORM\Column(name="description", type="text", nullable=false)
     */
    private $description;

    /**
     * Contains the name of the locale, e.g. `en_GB`.
     *
     * @var string $locale
     *
     * @ORM\Column(name="locale", type="string", length=15, nullable=false)
     */
    private $locale;

    /**
     * OWNING SIDE
     *
     * @var \Shopware\Models\Emotion\Preset $preset
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Emotion\Preset", inversedBy="translations", cascade={"persist"})
     * @ORM\JoinColumn(name="emotionID", referencedColumnName="id")
     */
    protected $preset;

    /**
     * Clone function for this model.
     */
    public function __clone()
    {
        $this->id = null;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
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
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @return Preset
     */
    public function getPreset()
    {
        return $this->preset;
    }

    /**
     * @param Preset $preset
     */
    public function setPreset(Preset $preset)
    {
        $this->preset = $preset;
    }
}
