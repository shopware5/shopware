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
     * @ORM\Column(name="configurator_set_id", type="integer", nullable=true)
     */
    private $configuratorSetId;

    /**
     * @var int
     *
     * @ORM\Column(name="parent_id", type="integer", nullable=true)
     */
    private $parentId;

    /**
     * @var int
     *
     * @ORM\Column(name="child_id", type="integer", nullable=true)
     */
    private $childId;

    /**
     * @var \Shopware\Models\Article\Configurator\Option
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Option", inversedBy="dependencyParents")
     * @ORM\JoinColumn(name="parent_id", referencedColumnName="id")
     */
    private $parentOption;

    /**
     * @var \Shopware\Models\Article\Configurator\Option
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Option", inversedBy="dependencyChildren")
     * @ORM\JoinColumn(name="child_id", referencedColumnName="id")
     */
    private $childOption;

    /**
     * @var \Shopware\Models\Article\Configurator\Set
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Configurator\Set", inversedBy="dependencies")
     * @ORM\JoinColumn(name="configurator_set_id", referencedColumnName="id")
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
     * @return \Shopware\Models\Article\Configurator\Option
     */
    public function getParentOption()
    {
        return $this->parentOption;
    }

    /**
     * @param \Shopware\Models\Article\Configurator\Option $parentOption
     */
    public function setParentOption($parentOption)
    {
        $this->parentOption = $parentOption;
    }

    /**
     * @return \Shopware\Models\Article\Configurator\Option
     */
    public function getChildOption()
    {
        return $this->childOption;
    }

    /**
     * @param \Shopware\Models\Article\Configurator\Option $childOption
     */
    public function setChildOption($childOption)
    {
        $this->childOption = $childOption;
    }

    /**
     * @return \Shopware\Models\Article\Configurator\Set
     */
    public function getConfiguratorSet()
    {
        return $this->configuratorSet;
    }

    /**
     * @param \Shopware\Models\Article\Configurator\Set $configuratorSet
     */
    public function setConfiguratorSet($configuratorSet)
    {
        $this->configuratorSet = $configuratorSet;
    }
}
