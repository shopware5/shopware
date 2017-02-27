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

namespace Shopware\Components\Emotion\Preset;

class PresetMetaData implements PresetMetaDataInterface
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var bool
     */
    private $premium = false;

    /**
     * @var bool
     */
    private $custom = true;

    /**
     * @var string
     */
    private $thumbnail;

    /**
     * @var string
     */
    private $preview;

    /**
     * @var array
     */
    private $translations = [];

    /**
     * @var array
     */
    private $presetData = [];

    /**
     * @var array
     */
    private $requiredPlugins = [];

    /**
     * @var bool
     */
    private $assetsImported = true;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }

    /**
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
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
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * @param array $translations
     */
    public function setTranslations(array $translations)
    {
        $this->translations = $translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getPresetData()
    {
        return $this->presetData;
    }

    /**
     * @param array $presetData
     */
    public function setPresetData(array $presetData)
    {
        $this->presetData = $presetData;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredPlugins()
    {
        return $this->requiredPlugins;
    }

    /**
     * @param array $requiredPlugins
     */
    public function setRequiredPlugins(array $requiredPlugins)
    {
        $this->requiredPlugins = $requiredPlugins;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetsImported()
    {
        return $this->assetsImported;
    }

    /**
     * @var bool
     */
    public function setAssetsImported($assetsImported)
    {
        $this->assetsImported = $assetsImported;
    }

    /**
     * @param array $data
     */
    public function fromArray(array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);

            if (method_exists($this, $method)) {
                $this->$method($value);
            }
        }
    }
}
