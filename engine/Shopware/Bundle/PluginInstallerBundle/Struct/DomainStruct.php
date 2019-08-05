<?php declare(strict_types=1);

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

namespace Shopware\Bundle\PluginInstallerBundle\Struct;

class DomainStruct implements \JsonSerializable
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var float
     */
    private $balance;

    /**
     * @var float
     */
    private $dispo;

    /**
     * @var bool
     */
    private $isPartner;

    public function __construct(int $id, string $domain, float $balance, float $dispo, bool $isPartner)
    {
        $this->id = $id;
        $this->domain = $domain;
        $this->balance = $balance;
        $this->dispo = $dispo;
        $this->isPartner = $isPartner;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function getBalance(): float
    {
        return $this->balance;
    }

    public function getDispo(): float
    {
        return $this->dispo;
    }

    public function isPartner(): bool
    {
        return $this->isPartner;
    }

    /**
     * {@inheritdoc}
     */
    public function jsonSerialize(): array
    {
        return get_object_vars($this);
    }
}
