<?php

declare(strict_types=1);
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our licensing model, this program can be used
 * under the terms of the GNU Affero General Public License, version 3.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission can be found at and in the LICENSE file you have received
 * along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore, any rights, title and interest in
 * our trademarks remain entirely with the shopware AG.
 */

namespace Shopware\Tests\Functional\Models\Site;

use Enlight_Components_Test_TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Site\Repository;
use Shopware\Models\Site\Site;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class SiteTest extends Enlight_Components_Test_TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private ModelManager $entityManager;

    private Repository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->getContainer()->get(ModelManager::class);
        $this->repository = $this->entityManager->getRepository(Site::class);
    }

    public function testSiteKeywordsMediumtextDatatype(): void
    {
        $site = $this->repository->find(3);
        static::assertInstanceOf(Site::class, $site);

        $keywords = require __DIR__ . '/../_assets/keywords.php';
        static::assertIsString($keywords);
        $site->setMetaKeywords($keywords);

        $this->entityManager->persist($site);
        $this->entityManager->flush($site);

        static::assertSame($keywords, $site->getMetaKeywords());
    }
}
