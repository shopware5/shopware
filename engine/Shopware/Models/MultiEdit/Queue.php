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

namespace Shopware\Models\MultiEdit;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware SwagMultiEdit Plugin - Queue Model
 *
 *
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_multi_edit_queue")
 */
class Queue extends ModelEntity
{
    /**
     * INVERSE SIDE
     *
     * @var ArrayCollection<\Shopware\Models\MultiEdit\QueueArticle>
     *
     * @ORM\OneToMany(targetEntity="Shopware\Models\MultiEdit\QueueArticle", mappedBy="queue", cascade={"persist"},  fetch="EXTRA_LAZY")
     */
    protected $articleDetails;

    /**
     * Unique identifier
     *
     * @var int
     *
     * @ORM\Column(name="id", type="integer", nullable=false)
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="resource", type="string", nullable=false)
     */
    private $resource;

    /**
     * @var string
     *
     * @ORM\Column(name="filter_string", type="string", nullable=false)
     */
    private $filterString;

    /**
     * @var string
     *
     * @ORM\Column(name="operations", type="string", nullable=false)
     */
    private $operations;

    /**
     * @var int
     *
     * @ORM\Column(name="items", type="integer", nullable=false)
     */
    private $initialSize;

    /**
     * @var bool
     *
     * @ORM\Column(name="active", type="boolean", nullable=false)
     */
    private $active = false;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @param string $resource
     */
    public function __construct($resource)
    {
        $this->resource = $resource;
        $this->articleDetails = new ArrayCollection();
    }

    public function setCreated(\DateTimeInterface $created)
    {
        $this->created = $created;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param string $filterString
     */
    public function setFilterString($filterString)
    {
        $this->filterString = $filterString;
    }

    /**
     * @return string
     */
    public function getFilterString()
    {
        return $this->filterString;
    }

    /**
     * @param string $operations
     */
    public function setOperations($operations)
    {
        $this->operations = $operations;
    }

    /**
     * @return string
     */
    public function getOperations()
    {
        return $this->operations;
    }

    /**
     * @param \Doctrine\Common\Collections\ArrayCollection $articleDetails
     */
    public function setArticleDetails($articleDetails)
    {
        $this->articleDetails = $articleDetails;
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getArticleDetails()
    {
        return $this->articleDetails;
    }

    /**
     * @param string $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

    /**
     * @param int $initialSize
     */
    public function setInitialSize($initialSize)
    {
        $this->initialSize = $initialSize;
    }

    /**
     * @return int
     */
    public function getInitialSize()
    {
        return $this->initialSize;
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param bool $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function getActive()
    {
        return $this->active;
    }
}
