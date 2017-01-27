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

use Doctrine\Common\Collections\ArrayCollection;
use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Shopware Emotion Model - Template
 *
 * @category   Shopware
 * @package    Shopware\Models
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity
 * @ORM\Table(name="s_emotion_presets")
 */
class Preset extends ModelEntity
{
    /**
     * Unique identifier field for the shopware emotion.
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains the technical name of the emotion preset.
     *
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Indicates if the preset contains elements only available through premium plugins.
     *
     * @var boolean $premium
     * @ORM\Column(name="premium", type="boolean", nullable=false)
     */
    private $premium;

    /**
     * Indicates if the preset is a custom user created preset.
     *
     * @var boolean $custom
     * @ORM\Column(name="custom", type="boolean", nullable=false)
     */
    private $custom;

    /**
     * Contains the thumbnail path
     *
     * @var string $thumbnail
     * @ORM\Column(name="thumbnail", type="text", nullable=true)
     */
    private $thumbnail;

    /**
     * Contains the preview image path
     *
     * @var string $preview
     * @ORM\Column(name="preview", type="text", nullable=true)
     */
    private $preview;

    /**
     * Contains the thumbnail path
     *
     * @var string $presetData
     * @ORM\Column(name="presetData", type="text", nullable=false)
     */
    private $presetData;

    /**
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\PresetTranslation", mappedBy="preset", orphanRemoval=true, cascade={"persist"})
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $translations;

    /**
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\PresetRequirement", mappedBy="preset", orphanRemoval=true, cascade={"persist"})
     * @var $requiredPlugins
     */
    protected $requiredPlugins;

    /**
     * Preset constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
        $this->requiredPlugins = new ArrayCollection();
        $this->custom = true;
    }

    /**
     * Clone function for this model.
     */
    public function __clone()
    {
        $this->id = null;

        $translations = new ArrayCollection();
        $requiredPlugins = new ArrayCollection();

        /** @var PresetTranslation $translation */
        foreach ($this->translations as $translation) {
            $newTranslation = clone $translation;
            $newTranslation->setPreset($this);

            $translations->add($newTranslation);
        }
        $this->translations = $translations;

        /** @var PresetRequirement $requiredPlugins */
        foreach ($this->requiredPlugins as $requiredPlugin) {
            $newRequirement = clone $requiredPlugin;
            $newRequirement->setPreset($this);

            $requiredPlugins->add($newRequirement);
        }
        $this->requiredPlugins = $requiredPlugins;

        $this->custom = true;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return boolean
     */
    public function getPremium()
    {
        return $this->premium;
    }

    /**
     * @param boolean $premium
     */
    public function setPremium($premium)
    {
        $this->premium = $premium;
    }

    /**
     * @return bool
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * @param boolean $custom
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;
    }

    /**
     * @return string
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * @param string $thumbnail
     */
    public function setThumbnail($thumbnail)
    {
        $this->thumbnail = $thumbnail;
    }

    /**
     * @return string
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * @param string $preview
     */
    public function setPreview($preview)
    {
        $this->preview = $preview;
    }

    /**
     * @return string
     */
    public function getPresetData()
    {
        return $this->presetData;
    }

    /**
     * @param string $presetData
     */
    public function setPresetData($presetData)
    {
        $this->presetData = $presetData;
    }

    /**
     * @param array $translations
     * @return ModelEntity
     */
    public function setTranslations(array $translations)
    {
        return $this->setOneToMany($translations, '\Shopware\Models\Emotion\PresetTranslation', 'translations', 'preset');
    }

    /**
     * @return ArrayCollection
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param array $requiredPlugins
     * @return ModelEntity
     */
    public function setRequiredPlugins(array $requiredPlugins)
    {
        return $this->setOneToMany($requiredPlugins, '\Shopware\Models\Emotion\PresetRequirement', 'requiredPlugins', 'preset');
    }

    /**
     * @return ArrayCollection
     */
    public function getRequiredPlugins()
    {
        return $this->requiredPlugins;
    }
}
