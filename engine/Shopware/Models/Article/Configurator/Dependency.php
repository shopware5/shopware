<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Models\Article\Configurator;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Entity()
 * @ORM\Table(name="s_article_configurator_dependencies")
 */
class Dependency extends ModelEntity
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="configurator_set_id", type="integer", nullable=false)
     */
    private $configuratorSetId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * @var int|null
     *
     * @ORM\Column(name="child_id", type="integer", nullable=true)
     */
    private $childId;

    /**
     * @var Option|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Option", inversedBy="dependencyParents")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parentOption;

    /**
     * @var Option|null
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Option", inversedBy="dependencyChildren")
     * @ORM\JoinColumn(name="child_id", referencedColumnName="id")
     */
    private $childOption;

    /**
     * @var Set
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Set", inversedBy="dependencies")
     * @ORM\JoinColumn(name="configurator_set_id", referencedColumnName="id", nullable=false)
     */
    private $configuratorSet;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Option|null
     */
    public function getParentOption()
    {
        return $this->parentOption;
    }

    /**
     * @param Option|null $parentOption
     */
    public function setParentOption($parentOption)
    {
        $this->parentOption = $parentOption;
    }

    /**
     * @return Option|null
     */
    public function getChildOption()
    {
        return $this->childOption;
    }

    /**
     * @param Option|null $childOption
     */
    public function setChildOption($childOption)
    {
        $this->childOption = $childOption;
    }

    /**
     * @return Set
     */
    public function getConfiguratorSet()
    {
        return $this->configuratorSet;
    }

    /**
     * @param Set $configuratorSet
     */
    public function setConfiguratorSet($configuratorSet)
    {
        $this->configuratorSet = $configuratorSet;
    }
}
