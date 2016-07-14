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
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Shopware Emotion Model - Template
 *
 * @category   Shopware
 * @package    Shopware\Models
 * @copyright  Copyright (c) shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity
 * @ORM\Table(name="s_emotion_templates")
 * @ORM\HasLifecycleCallbacks
 */
class Template extends ModelEntity
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
     * Contains the name of the emotion.
     *
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     * @ORM\Column(name="file", type="string", length=255, nullable=false)
     */
    private $file;

    /**
     * @var ArrayCollection
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\Emotion", mappedBy="template")
     */
    protected $emotions;

    /**
     * Clone function for this model.
     */
    public function __clone()
    {
        $this->id = null;
        $this->emotions = new ArrayCollection();
    }

    public function __construct()
    {
        $this->emotions = new ArrayCollection();
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
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * @param string $file
     */
    public function setFile($file)
    {
        $this->file = $file;
    }
}
