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
 * Shopware Emotion Model - Grid
 *
 * @category  Shopware
 * @package   Shopware\Models\Emotion
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 *
 * @ORM\Entity
 * @ORM\Table(name="s_emotion_grid")
 * @ORM\HasLifecycleCallbacks
 */
class Grid extends ModelEntity
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
     * @var integer $cols
     *
     * @ORM\Column(name="cols", type="integer", nullable=false)
     */
    private $cols = 4;

    /**
     * @var integer $rows
     *
     * @ORM\Column(name="rows", type="integer", nullable=false)
     */
    private $rows = 20;

    /**
     * @var integer $cellHeight
     *
     * @ORM\Column(name="cell_height", type="integer", nullable=false)
     */
    private $cellHeight = 185;

    /**
     * @var integer $articleHeight
     *
     * @ORM\Column(name="article_height", type="integer", nullable=false)
     */
    private $articleHeight = 2;

    /**
     * @var integer $gutter
     *
     * @ORM\Column(name="gutter", type="integer", nullable=false)
     */
    private $gutter = 10;

    /**
     * @ORM\OneToMany(targetEntity="Shopware\Models\Emotion\Emotion", mappedBy="grid")
     * @var ArrayCollection
     */
    protected $emotions;

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
     * @param int $articleHeight
     */
    public function setArticleHeight($articleHeight)
    {
        $this->articleHeight = $articleHeight;
    }

    /**
     * @return int
     */
    public function getArticleHeight()
    {
        return $this->articleHeight;
    }

    /**
     * @param int $cellHeight
     */
    public function setCellHeight($cellHeight)
    {
        $this->cellHeight = $cellHeight;
    }

    /**
     * @return int
     */
    public function getCellHeight()
    {
        return $this->cellHeight;
    }

    /**
     * @param int $cols
     */
    public function setCols($cols)
    {
        $this->cols = $cols;
    }

    /**
     * @return int
     */
    public function getCols()
    {
        return $this->cols;
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
     * @param int $rows
     */
    public function setRows($rows)
    {
        $this->rows = $rows;
    }

    /**
     * @return int
     */
    public function getRows()
    {
        return $this->rows;
    }

    /**
     * Clone function for this model.
     */
    public function __clone()
    {
        $this->id = null;
        $this->emotions = new ArrayCollection();
    }

    /**
     * @param int $gutter
     */
    public function setGutter($gutter)
    {
        $this->gutter = $gutter;
    }

    /**
     * @return int
     */
    public function getGutter()
    {
        return $this->gutter;
    }
}
