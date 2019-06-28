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
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Shopware Emotion Model - Template
 *
 *
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_emotion_presets")
 */
class Preset extends ModelEntity
{
    /**
     * @var \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Emotion\PresetTranslation>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\PresetTranslation", mappedBy="preset", orphanRemoval=true, cascade={"persist"})
     */
    protected $translations;

    /**
     * @var string
     *
     * @ORM\Column(name="required_plugins", type="text", nullable=false)
     */
    protected $requiredPlugins;

    /**
     * Unique identifier field for the shopware emotion.
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains the technical name of the emotion preset.
     *
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * Indicates if the preset contains elements only available through premium plugins.
     *
     * @var bool
     *
     * @ORM\Column(name="premium", type="boolean", nullable=false)
     */
    private $premium = false;

    /**
     * Indicates if the preset is a custom user created preset.
     *
     * @var bool
     *
     * @ORM\Column(name="custom", type="boolean", nullable=false)
     */
    private $custom = true;

    /**
     * Contains the thumbnail path
     *
     * @var string
     *
     * @ORM\Column(name="thumbnail", type="text", nullable=true)
     */
    private $thumbnail;

    /**
     * Contains the preview image path
     *
     * @var string
     *
     * @ORM\Column(name="preview", type="text", nullable=true)
     */
    private $preview;

    /**
     * Contains the thumbnail path
     *
     * @var string
     *
     * @Assert\NotBlank()
     *
     * @ORM\Column(name="preset_data", type="text", nullable=false)
     */
    private $presetData;

    /**
     * Contains the asset data for imports
     *
     * @var bool
     *
     * @ORM\Column(name="assets_imported", type="boolean", nullable=false)
     */
    private $assetsImported = true;

    /**
     * Contains the info if preset is hidden for internal im/export use.
     *
     * @var bool
     *
     * @ORM\Column(name="hidden", type="boolean", nullable=false)
     */
    private $hidden = false;

    /**
     * @var string
     *
     * @ORM\Column(name="emotion_translations", type="text", nullable=false)
     */
    private $emotionTranslations;

    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }

    /**
     * Clone function for this model.
     */
    public function __clone()
    {
        $this->id = null;

        $translations = new ArrayCollection();

        /** @var PresetTranslation $translation */
        foreach ($this->translations as $translation) {
            $newTranslation = clone $translation;
            $newTranslation->setPreset($this);

            $translations->add($newTranslation);
        }
        $this->translations = $translations;
        $this->custom = true;
        $this->hidden = false;
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
     * @return bool
     */
    public function getPremium()
    {
        return $this->premium;
    }

    /**
     * @param bool $premium
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
     * @param bool $custom
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
     * @return bool
     */
    public function getAssetsImported()
    {
        return $this->assetsImported;
    }

    /**
     * @param bool $assetsImported
     */
    public function setAssetsImported($assetsImported)
    {
        $this->assetsImported = $assetsImported;
    }

    /**
     * @return bool
     */
    public function getHidden()
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden($hidden)
    {
        $this->hidden = $hidden;
    }

    /**
     * @param \Shopware\Models\Emotion\PresetTranslation[] $translations
     *
     * @return Preset
     */
    public function setTranslations(array $translations)
    {
        return $this->setOneToMany($translations, \Shopware\Models\Emotion\PresetTranslation::class, 'translations', 'preset');
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection<\Shopware\Models\Emotion\PresetTranslation>
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param string $requiredPlugins
     */
    public function setRequiredPlugins($requiredPlugins)
    {
        $this->requiredPlugins = $requiredPlugins;
    }

    /**
     * @return string
     */
    public function getRequiredPlugins()
    {
        return $this->requiredPlugins;
    }

    /**
     * @return string
     */
    public function getEmotionTranslations()
    {
        return $this->emotionTranslations;
    }

    /**
     * @param string $emotionTranslations
     */
    public function setEmotionTranslations($emotionTranslations)
    {
        $this->emotionTranslations = $emotionTranslations;
    }
}
