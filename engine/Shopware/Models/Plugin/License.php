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

namespace Shopware\Models\Plugin;

use Doctrine\ORM\Mapping as ORM;
use Shopware\Components\Model\ModelEntity;

/**
 * @ORM\Table(name="s_core_licenses")
 * @ORM\Entity()
 */
class License extends ModelEntity
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
     * @var string
     *
     * @ORM\Column(name="module", type="string", nullable=false)
     */
    private $module;

    /**
     * @var string
     *
     * @ORM\Column(name="host", type="string", nullable=false)
     */
    private $host;

    /**
     * @var string
     *
     * @ORM\Column(name="label", type="string", nullable=false)
     */
    private $label;

    /**
     * @var string
     *
     * @ORM\Column(name="license", type="string", nullable=false)
     */
    private $license;

    /**
     * @var string
     *
     * @ORM\Column(name="version", type="string", nullable=false)
     */
    private $version;

    /**
     * @var string
     * @ORM\Column(name="notation", type="string", nullable=false)
     */
    private $notation;

    /**
     * @var int
     *
     * @ORM\Column(name="type", type="integer", nullable=false)
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="source", type="integer", nullable=false)
     */
    private $source;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="added", type="date", nullable=true)
     */
    private $added;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="creation", type="date", nullable=true)
     */
    private $creation;

    /**
     * @var \DateTimeInterface
     *
     * @ORM\Column(name="expiration", type="date", nullable=true)
     */
    private $expiration;

    /**
     * @var int
     *
     * @ORM\Column(name="active", type="integer", nullable=true)
     */
    private $active;

    /**
     * @var int
     *
     * @ORM\Column(name="plugin_id", type="integer", nullable=true)
     */
    private $pluginId;

    /**
     * @var \Shopware\Models\Plugin\Plugin
     *
     * @ORM\ManyToOne(targetEntity="Shopware\Models\Plugin\Plugin", inversedBy="licenses")
     * @ORM\JoinColumn(name="plugin_id", referencedColumnName="id")
     */
    private $plugin;

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $active
     */
    public function setActive($active)
    {
        $this->active = $active;
    }

    /**
     * @return int
     */
    public function getActive()
    {
        return $this->active;
    }

    /**
     * @param \DateTimeInterface $added
     */
    public function setAdded($added)
    {
        $this->added = $added;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getAdded()
    {
        return $this->added;
    }

    /**
     * @param \DateTimeInterface $creation
     */
    public function setCreation($creation)
    {
        $this->creation = $creation;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getCreation()
    {
        return $this->creation;
    }

    /**
     * @param \DateTimeInterface $expiration
     */
    public function setExpiration($expiration)
    {
        $this->expiration = $expiration;
    }

    /**
     * @return \DateTimeInterface
     */
    public function getExpiration()
    {
        return $this->expiration;
    }

    /**
     * @param string $host
     */
    public function setHost($host)
    {
        $this->host = $host;
    }

    /**
     * @return string
     */
    public function getHost()
    {
        return $this->host;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $license
     */
    public function setLicense($license)
    {
        $this->license = $license;
    }

    /**
     * @return string
     */
    public function getLicense()
    {
        return $this->license;
    }

    /**
     * @param string $module
     */
    public function setModule($module)
    {
        $this->module = $module;
    }

    /**
     * @return string
     */
    public function getModule()
    {
        return $this->module;
    }

    /**
     * @param string $notation
     */
    public function setNotation($notation)
    {
        $this->notation = $notation;
    }

    /**
     * @return string
     */
    public function getNotation()
    {
        return $this->notation;
    }

    /**
     * @param Plugin $plugin
     */
    public function setPlugin($plugin)
    {
        $this->plugin = $plugin;
    }

    /**
     * @return Plugin
     */
    public function getPlugin()
    {
        return $this->plugin;
    }

    /**
     * @param int $source
     */
    public function setSource($source)
    {
        $this->source = $source;
    }

    /**
     * @return int
     */
    public function getSource()
    {
        return $this->source;
    }

    /**
     * @param int $type
     */
    public function setType($type)
    {
        $this->type = $type;
    }

    /**
     * @return int
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->version = $version;
    }

    /**
     * @return string
     */
    public function getVersion()
    {
        return $this->version;
    }
}
