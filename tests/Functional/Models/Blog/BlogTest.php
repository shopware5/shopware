<?php

declare(strict_types=1);
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

namespace Shopware\Tests\Functional\Models\Article;

use Enlight_Components_Test_TestCase;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Blog\Blog;
use Shopware\Models\Blog\Repository;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;

class BlogTest extends Enlight_Components_Test_TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    private ModelManager $entityManager;

    private Repository $repository;

    protected function setUp(): void
    {
        parent::setUp();

        $this->entityManager = $this->getContainer()->get(ModelManager::class);
        $this->repository = $this->entityManager->getRepository(Blog::class);
    }

    public function testBlogKeywordsMediumtextDatatype(): void
    {
        $blog = $this->repository->find(3);
        static::assertInstanceOf(Blog::class, $blog);

        $keywords = require __DIR__ . '/../_assets/keywords.php';
        static::assertIsString($keywords);
        $blog->setMetaKeyWords($keywords);

        $this->entityManager->persist($blog);
        $this->entityManager->flush($blog);

        static::assertSame($keywords, $blog->getMetaKeyWords());
    }
}
