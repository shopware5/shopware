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

namespace   Shopware\Models\Newsletter\ContainerType;

use Shopware\Components\Model\ModelEntity;
use Doctrine\ORM\Mapping as ORM;

/**
 * Shopware text model represents a link container type.
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_campaigns_links")
 */
class Link extends ModelEntity
{
    /**
     * Autoincrement ID
     *
     * @var integer $id
     *
     * @ORM\Id
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * ID of the container this model belongs to
     *
     * @var integer $containerId
     *
     * @ORM\Column(name="parentID", type="integer", length=11, nullable=true)
     */
    private $containerId = null;

    /**
     * OWNING SIDE
     * Owning side of relation between container type 'article' and parent container
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Newsletter\Container", inversedBy="links")
     * @ORM\JoinColumn(name="parentID", referencedColumnName="id")
     * @var \Shopware\Models\Newsletter\Container
     */
    protected $container;

    /**
     * Description of the link / link text
     * @var string $description
     *
     * @ORM\Column(name="description", type="string", nullable=false)
     */
    private $description;

    /**
     * the actual link
     * @var string $link
     *
     * @ORM\Column(name="link", type="string", nullable=false)
     */
    private $link;

    /**
     * Link target, e.g. _blank / _parent
     * @var string $target
     *
     * @ORM\Column(name="target", type="string", nullable=false)
     */
    private $target;

    /**
     * @var integer position
     *
     * @ORM\Column(name="position", type="integer", length=255, nullable=false)
     */
    private $position;

    /**
     * @param string $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param string $link
     */
    public function setLink($link)
    {
        $this->link = $link;
    }

    /**
     * @return string
     */
    public function getLink()
    {
        return $this->link;
    }

    /**
     * @param int $position
     */
    public function setPosition($position)
    {
        $this->position = $position;
    }

    /**
     * @return int
     */
    public function getPosition()
    {
        return $this->position;
    }

    /**
     * @param string $target
     */
    public function setTarget($target)
    {
        $this->target = $target;
    }

    /**
     * @return string
     */
    public function getTarget()
    {
        return $this->target;
    }

    /**
     * @param \Shopware\Models\Newsletter\Container $container
     * @return \Shopware\Models\Newsletter\Container
     */
    public function setContainer($container)
    {
        $this->container = $container;
        $container->setType('ctLinks');
//        return $this->setOneToOne($container, '\Shopware\Models\Newsletter\Container', 'container', 'text');
    }

    /**
     * @return \Shopware\Models\Newsletter\Container
     */
    public function getContainer()
    {
        return $this->container;
    }
}
