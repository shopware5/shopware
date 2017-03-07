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

declare(strict_types=1);

namespace Shopware\Bundle\CartBundle\Domain\Payment;

use Shopware\Bundle\CartBundle\Domain\CloneTrait;
use Shopware\Bundle\CartBundle\Domain\JsonSerializableTrait;
use Shopware\Bundle\StoreFrontBundle\Struct\Extendable;

class PaymentMethod extends Extendable
{
    use CloneTrait, JsonSerializableTrait;

    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $label;

    /**
     * @var string
     */
    protected $template;

    /**
     * @var string
     */
    protected $description;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var bool
     */
    protected $hidden;

    /**
     * @var float
     */
    protected $percentDebit;

    /**
     * @var float
     */
    protected $surcharge;

    /**
     * @var int
     */
    protected $position;

    /**
     * @var bool
     */
    protected $active;

    /**
     * @var bool
     */
    protected $esdActive;

    /**
     * @var string|null
     */
    protected $iFrameUrl;

    /**
     * @var string|null
     */
    protected $action;

    /**
     * @var int|null
     */
    protected $pluginId;

    /**
     * @var int|null
     */
    protected $source;

    /**
     * @var bool
     */
    protected $mobileInactive;


    /**
     * @param int $id
     * @param string $name
     * @param string $label
     */
    public function __construct(int $id, string $name, string $label, string $class)
    {
        $this->id = $id;
        $this->name = $name;
        $this->label = $label;
        $this->class = $class;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel(string $label): void
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getTemplate(): string
    {
        return $this->template;
    }

    /**
     * @param string $template
     */
    public function setTemplate(string $template): void
    {
        $this->template = $template;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return string
     */
    public function getClass(): string
    {
        return $this->class;
    }

    /**
     * @param string $class
     */
    public function setClass(string $class): void
    {
        $this->class = $class;
    }

    /**
     * @return string
     */
    public function getTable(): string
    {
        return $this->table;
    }

    /**
     * @param string $table
     */
    public function setTable(string $table): void
    {
        $this->table = $table;
    }

    /**
     * @return bool
     */
    public function isHidden(): bool
    {
        return $this->hidden;
    }

    /**
     * @param bool $hidden
     */
    public function setHidden(bool $hidden): void
    {
        $this->hidden = $hidden;
    }

    /**
     * @return float
     */
    public function getPercentDebit(): float
    {
        return $this->percentDebit;
    }

    /**
     * @param float $percentDebit
     */
    public function setPercentDebit(float $percentDebit): void
    {
        $this->percentDebit = $percentDebit;
    }

    /**
     * @return float
     */
    public function getSurcharge(): float
    {
        return $this->surcharge;
    }

    /**
     * @param float $surcharge
     */
    public function setSurcharge(float $surcharge): void
    {
        $this->surcharge = $surcharge;
    }

    /**
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * @param int $position
     */
    public function setPosition(int $position): void
    {
        $this->position = $position;
    }

    /**
     * @return bool
     */
    public function isActive(): bool
    {
        return $this->active;
    }

    /**
     * @param bool $active
     */
    public function setActive(bool $active): void
    {
        $this->active = $active;
    }

    /**
     * @return bool
     */
    public function isEsdActive(): bool
    {
        return $this->esdActive;
    }

    /**
     * @param bool $esdActive
     */
    public function setEsdActive(bool $esdActive): void
    {
        $this->esdActive = $esdActive;
    }

    /**
     * @return null|string
     */
    public function getIFrameUrl()
    {
        return $this->iFrameUrl;
    }

    /**
     * @param null|string $iFrameUrl
     */
    public function setIFrameUrl(?string $iFrameUrl): void
    {
        $this->iFrameUrl = $iFrameUrl;
    }

    /**
     * @return null|string
     */
    public function getAction():? string
    {
        return $this->action;
    }

    /**
     * @param null|string $action
     */
    public function setAction(?string $action): void
    {
        $this->action = $action;
    }

    /**
     * @return int|null
     */
    public function getPluginId():? int
    {
        return $this->pluginId;
    }

    /**
     * @param int|null $pluginId
     */
    public function setPluginId(?int $pluginId): void
    {
        $this->pluginId = $pluginId;
    }

    /**
     * @return int|null
     */
    public function getSource():? int
    {
        return $this->source;
    }

    /**
     * @param int|null $source
     */
    public function setSource(?int $source): void
    {
        $this->source = $source;
    }

    /**
     * @return bool
     */
    public function isMobileInactive(): bool
    {
        return $this->mobileInactive;
    }

    /**
     * @param bool $mobileInactive
     */
    public function setMobileInactive(bool $mobileInactive): void
    {
        $this->mobileInactive = $mobileInactive;
    }
}
