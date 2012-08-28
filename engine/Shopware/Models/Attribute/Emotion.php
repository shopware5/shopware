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
 * Shopware\Models\Attribute\Emotion
 *
 * @ORM\Table(name="s_emotion_attributes")
 * @ORM\Entity
 */
class Emotion extends ModelEntity
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
     * @var integer $emotionId
     *
     * @ORM\Column(name="emotionID", type="integer", nullable=true)
     */
    private $emotionId = null;

    /**
     * @var Shopware\Models\Emotion\Emotion
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Emotion\Emotion", inversedBy="attribute")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="emotionID", referencedColumnName="id")
     * })
     */
    private $emotion;

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
     * Set emotion
     *
     * @param Shopware\Models\Emotion\Emotion $emotion
     * @return Emotion
     */
    public function setEmotion(\Shopware\Models\Emotion\Emotion $emotion = null)
    {
        $this->emotion = $emotion;
        return $this;
    }

    /**
     * Get emotion
     *
     * @return Shopware\Models\Emotion\Emotion
     */
    public function getEmotion()
    {
        return $this->emotion;
    }

    /**
     * Set emotionId
     *
     * @param integer $emotionId
     * @return Emotion
     */
    public function setEmotionId($emotionId)
    {
        $this->emotionId = $emotionId;
        return $this;
    }

    /**
     * Get emotionId
     *
     * @return integer
     */
    public function getEmotionId()
    {
        return $this->emotionId;
    }
}
