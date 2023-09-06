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

namespace Shopware\Tests\Unit\Bundle\SitemapBundle;

use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\Adapters\LocalAdapterFactory;
use Shopware\Bundle\SitemapBundle\Service\SitemapLister;
use Shopware\Bundle\SitemapBundle\Service\SitemapNameGenerator;
use Shopware\Bundle\SitemapBundle\Struct\Sitemap;
use Shopware\Components\DependencyInjection\Container;
use Shopware\Components\Filesystem\PublicUrlGenerator;

class SitemapListenerTest extends TestCase
{
    private SitemapLister $listener;

    private SitemapNameGenerator $generator;

    private Filesystem $fileSystem;

    protected function setUp(): void
    {
        parent::setUp();

        $factory = new LocalAdapterFactory();

        $this->fileSystem = new Filesystem($factory->create([
            'root' => sys_get_temp_dir(),
        ]));

        $this->generator = new SitemapNameGenerator($this->fileSystem);

        $generator = new PublicUrlGenerator(new Container(), '', 'https//foo.de', 'foo');
        $this->listener = new SitemapLister($this->fileSystem, $generator);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->fileSystem->deleteDir('shop-1');
        $this->fileSystem->deleteDir('shop-2');
    }

    public function testListEmptyFolder(): void
    {
        static::assertEmpty($this->listener->getSitemaps(1));
    }

    public function testListWithSitemap(): void
    {
        $file = $this->generator->getSitemapFilename(1);
        $this->fileSystem->write($file, '');

        $sitemaps = $this->listener->getSitemaps(1);
        static::assertNotEmpty($sitemaps);

        static::assertInstanceOf(Sitemap::class, $sitemaps[0]);

        // Subshop specific sitemaps
        static::assertEmpty($this->listener->getSitemaps(2));
        static::assertNotEmpty($this->listener->getSitemaps(1));
    }
}
