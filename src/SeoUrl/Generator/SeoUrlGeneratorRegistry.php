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

namespace Shopware\SeoUrl\Generator;

use Doctrine\DBAL\Connection;
use Shopware\Context\TranslationContext;
use Shopware\SeoUrl\Gateway\SeoUrlRepository;

class SeoUrlGeneratorRegistry
{
    const LIMIT = 200;

    /**
     * @var SeoUrlGeneratorInterface[]
     */
    private $generators;

    /**
     * @var SeoUrlRepository
     */
    private $repository;

    /**
     * @var Connection
     */
    private $connection;

    public function __construct(array $generators, SeoUrlRepository $repository, Connection $connection)
    {
        $this->generators = $generators;
        $this->repository = $repository;
        $this->connection = $connection;
    }

    public function generate(int $shopId, TranslationContext $context): void
    {
        foreach ($this->generators as $generator) {
            $this->connection->transactional(
                function () use ($shopId, $generator, $context) {
                    $offset = 0;

                    while ($routes = $generator->fetch($shopId, $context, $offset, self::LIMIT)) {
                        $this->repository->create($routes);
                        $offset += self::LIMIT;
                    }
                }
            );
        }
    }
}
