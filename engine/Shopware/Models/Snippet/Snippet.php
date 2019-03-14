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

namespace Shopware\Models\Snippet;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * Shopware snippet model represents a single snippet
 *
 * Indices:
 * <code>
 *   - PRIMARY KEY (`id`)
 *   - UNIQUE KEY `namespace` (`namespace`,`shopID`,`name`,`localeID`)
 * </code>
 *
 * @ORM\Entity(repositoryClass="SnippetRepository")
 * @ORM\Table(name="s_core_snippets")
 * @ORM\HasLifecycleCallbacks()
 */
class Snippet extends ModelEntity
{
    /**
     * @var bool
     *
     * @ORM\Column(name="dirty", type="boolean", nullable=false)
     */
    protected $dirty = false;

    /**
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
     * @ORM\Column(name="namespace", type="string", length=255, nullable=false)
     */
    private $namespace;

    /**
     * @var int
     *
     * @ORM\Column(name="shopID", type="integer", nullable=false)
     */
    private $shopId;

    /**
     * @var int
     *
     * @ORM\Column(name="localeID", type="integer", nullable=false)
     */
    private $localeId;

    /**
     * @var string
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=false)
     */
    private $name;

    /**
     * @var string
     *
     * @ORM\Column(name="value", type="text", nullable=false)
     */
    private $value;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="created", type="datetime", nullable=false)
     */
    private $created;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="updated", type="datetime", nullable=false)
     */
    private $updated;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param string $namespace
     *
     * @return \Shopware\Models\Snippet\Snippet
     */
    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @return string
     */
    public function getNamespace()
    {
        return $this->namespace;
    }

    /**
     * @param int $shopid
     *
     * @return \Shopware\Models\Snippet\Snippet
     */
    public function setShopId($shopid)
    {
        $this->shopId = $shopid;

        return $this;
    }

    /**
     * @return int
     */
    public function getShopId()
    {
        return $this->shopId;
    }

    /**
     * @param int $localeid
     *
     * @return \Shopware\Models\Snippet\Snippet
     */
    public function setLocaleId($localeid)
    {
        $this->localeId = $localeid;

        return $this;
    }

    /**
     * @return int
     */
    public function getLocaleId()
    {
        return $this->localeId;
    }

    /**
     * @param string $name
     *
     * @return \Shopware\Models\Snippet\Snippet
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $value
     *
     * @return \Shopware\Models\Snippet\Snippet
     */
    public function setValue($value)
    {
        $this->value = $value;

        return $this;
    }

    /**
     * @return string
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param \DateTimeInterface|string $created
     *
     * @return \Shopware\Models\Snippet\Snippet
     */
    public function setCreated($created = 'now')
    {
        if (!$created instanceof \DateTimeInterface) {
            $this->created = new \DateTime($created);
        } else {
            $this->created = $created;
        }

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreated()
    {
        return $this->created;
    }

    /**
     * @param \DateTimeInterface|string $updated
     *
     * @return \Shopware\Models\Snippet\Snippet
     */
    public function setUpdated($updated = 'now')
    {
        if (!$updated instanceof \DateTimeInterface) {
            $this->updated = new \DateTime($updated);
        } else {
            $this->updated = $updated;
        }

        return $this;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getUpdated()
    {
        return $this->updated;
    }

    /**
     * Sets created on pre persist
     *
     * @ORM\PrePersist()
     */
    public function onPrePersist()
    {
        $this->created = new \DateTime('now');
        $this->updated = new \DateTime('now');
    }

    /**
     * Sets update on pre update
     *
     * @ORM\PreUpdate()
     */
    public function onPreUpdate()
    {
        $this->updated = new \DateTime('now');
    }

    /**
     * @param bool $dirty
     */
    public function setDirty($dirty)
    {
        $this->dirty = $dirty;
    }

    /**
     * @return bool
     */
    public function getDirty()
    {
        return $this->dirty;
    }
}
