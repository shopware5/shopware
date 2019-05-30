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

namespace Shopware\Models\Article\Image;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware Article Image Rule model.
 *
 * @ORM\Entity()
 * @ORM\Table(name="s_article_img_mapping_rules")
 */
class Rule extends ModelEntity
{
    /**
     * OWNING SIDE - BI DIRECTIONAL
     *
     * @var \Shopware\Models\Article\Image\Mapping
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Article\Image\Mapping", inversedBy="rules")
     * @ORM\JoinColumn(name="mapping_id", referencedColumnName="id")
     */
    protected $mapping;

    /**
     * OWNING SIDE - UNI DIRECTIONAL
     *
     * @var \Shopware\Models\Article\Configurator\Option
     *
     * @ORM\OneToOne(targetEntity="Shopware\Models\Article\Configurator\Option")
     * @ORM\JoinColumn(name="option_id", referencedColumnName="id")
     */
    protected $option;

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
     * @ORM\Column(name="mapping_id", type="integer", nullable=false)
     */
    private $mappingId;

    /**
     * @var int
     *
     * @ORM\Column(name="option_id", type="integer", nullable=false)
     */
    private $optionId;

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
    public function getOption()
    {
        return $this->option;
    }

    /**
     * @param \Shopware\Models\Article\Configurator\Option $option
     */
    public function setOption($option)
    {
        $this->option = $option;
    }

    /**
     * @return \Shopware\Models\Article\Image\Mapping
     */
    public function getMapping()
    {
        return $this->mapping;
    }

    /**
     * @param \Shopware\Models\Article\Image\Mapping $mapping
     */
    public function setMapping($mapping)
    {
        $this->mapping = $mapping;
    }
}
