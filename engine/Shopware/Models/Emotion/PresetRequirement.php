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
 * Shopware Emotion Model - Preset requirement
 *
 * Contains information about by preset required plugins.
 *
 * @category   Shopware
 * @package    Shopware\Models
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity
 * @ORM\Table(name="s_emotion_preset_requirements")
 */
class PresetRequirement extends ModelEntity
{
    /**
     * Unique identifier field for the shopware emotion preset requirement.
     *
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * Contains the technical name of the emotion preset requirement (i.e. plugin).
     *
     * @var string $technicalName
     *
     * @ORM\Column(name="technical_name", type="string", length=255, nullable=false)
     */
    private $technicalName;

    /**
     * Contains the label of the emotion preset requirement (i.e. plugin name).
     *
     * @var string $label
     *
     * @ORM\Column(name="label", type="string", length=255, nullable=false)
     */
    private $label;

    /**
     * @var \Shopware\Models\Emotion\Preset $preset
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Emotion\Preset", inversedBy="requirements")
     * @ORM\JoinColumn(name="presetID", referencedColumnName="id")
     */
    private $preset;

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
     * @param string $technicalName
     */
    public function setTechnicalName($technicalName)
    {
        $this->technicalName = $technicalName;
    }

    /**
     * @return string
     */
    public function getTechnicalName()
    {
        return $this->technicalName;
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
