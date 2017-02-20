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
    private $premium;

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
    private $translations;

    /**
     * @var array
     */
    private $presetData;

    /**
     * @var array
     */
    private $requiredPlugins;

    /**
     * @var bool
     */
    private $assetsImported;

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * {@inheritdoc}
     */
    public function getPremium()
    {
        return $this->premium;
    }

    /**
     * {@inheritdoc}
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * {@inheritdoc}
     */
    public function getThumbnail()
    {
        return $this->thumbnail;
    }

    /**
     * {@inheritdoc}
     */
    public function getPreview()
    {
        return $this->preview;
    }

    /**
     * {@inheritdoc}
     */
    public function getTranslations()
    {
        return $this->translations;
    }

    /**
     * {@inheritdoc}
     */
    public function getPresetData()
    {
        return $this->presetData;
    }

    /**
     * {@inheritdoc}
     */
    public function getRequiredPlugins()
    {
        return $this->requiredPlugins;
    }

    /**
     * {@inheritdoc}
     */
    public function getAssetsImported()
    {
        return $this->assetsImported;
    }
}
