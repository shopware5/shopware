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

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware SwagMultiEdit Plugin - Backup Model
 *
 *
 *
 * @ORM\Entity(repositoryClass="Repository")
 * @ORM\Table(name="s_multi_edit_backup")
 */
class Backup extends ModelEntity
{
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
     * @ORM\Column(name="filter_string", type="string", nullable=false)
     */
    private $filterString;

    /**
     * @var string
     *
     * @ORM\Column(name="operation_string", type="string", nullable=false)
     */
    private $operationString;

    /**
     * @var int
     *
     * @ORM\Column(name="items", type="integer", nullable=false)
     */
    private $items;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="date", type="datetime", nullable=false)
     */
    private $date;

    /**
     * @var int
     *
     * @ORM\Column(name="size", type="integer", nullable=false)
     */
    private $size;

    /**
     * @var string
     *
     * @ORM\Column(name="path", type="string", nullable=false)
     */
    private $path;

    /**
     * @var string
     *
     * @ORM\Column(name="hash", type="string", nullable=false)
     */
    private $hash;

    /**
     * @param \DateTimeInterface $date
     */
    public function setDate($date)
    {
        $this->date = $date;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getDate()
    {
        return $this->date;
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
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $items
     */
    public function setItems($items)
    {
        $this->items = $items;
    }

    /**
     * @return int
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * @param string $hash
     */
    public function setHash($hash)
    {
        $this->hash = $hash;
    }

    /**
     * @return string
     */
    public function getHash()
    {
        return $this->hash;
    }

    /**
     * @param string $operations
     */
    public function setOperationString($operations)
    {
        $this->operationString = $operations;
    }

    /**
     * @return string
     */
    public function getOperationString()
    {
        return $this->operationString;
    }

    /**
     * @param string $path
     */
    public function setPath($path)
    {
        $this->path = $path;
    }

    /**
     * @return string
     */
    public function getPath()
    {
        return $this->path;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->size = $size;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->size;
    }
}
