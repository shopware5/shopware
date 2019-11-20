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

namespace Shopware\Tests\Functional\Bundle\ContentTypeBundle\FieldResolver;

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\ContentTypeBundle\Field\MediaField;
use Shopware\Bundle\ContentTypeBundle\Field\MediaGrid;
use Shopware\Bundle\ContentTypeBundle\FieldResolver\AbstractResolver;
use Shopware\Bundle\ContentTypeBundle\FieldResolver\MediaResolver;
use Shopware\Bundle\ContentTypeBundle\Structs\Field;

class MediaResolverTest extends TestCase
{
    /**
     * @var AbstractResolver
     */
    private $service;

    /**
     * @var Field
     */
    private $field;

    protected function setUp(): void
    {
        $this->service = Shopware()->Container()->get(MediaResolver::class);
        $this->field = new Field();
        $this->field->setType(new MediaField());
    }

    public function testResolve(): void
    {
        $id = (int) Shopware()->Models()->getConnection()->fetchColumn('SELECT id from s_media LIMIT 1');

        $this->service->add($id, $this->field);
        $this->service->resolve();

        $media = $this->service->get($id, $this->field);
        static::assertIsArray($media);
        static::assertEquals($id, $media['id']);
    }

    public function testMultiResolve(): void
    {
        $idsArray = Shopware()->Models()->getConnection()->executeQuery('SELECT id from s_media LIMIT 5')->fetchAll(\PDO::FETCH_COLUMN);
        $ids = implode('|', $idsArray);
        $this->field->setType(new MediaGrid());

        $this->service->add($ids, $this->field);
        $this->service->resolve();

        $media = $this->service->get($ids, $this->field);
        static::assertCount(count($idsArray), $media);
    }
}
