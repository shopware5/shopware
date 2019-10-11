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

namespace Shopware\Tests\Unit\Bundle\SitemapBundle;

use League\Flysystem\Filesystem;
use PHPUnit\Framework\TestCase;
use Shopware\Bundle\MediaBundle\Adapters\LocalAdapterFactory;
use Shopware\Bundle\SitemapBundle\Service\SitemapNameGenerator;

class SitemapNameGeneratorTest extends TestCase
{
    /**
     * @var SitemapNameGenerator
     */
    private $generator;

    /**
     * @var Filesystem
     */
    private $fs;

    protected function setUp(): void
    {
        parent::setUp();
        $factory = new LocalAdapterFactory();

        $this->fs = new Filesystem($factory->create([
            'root' => sys_get_temp_dir(),
        ]));

        $this->generator = new SitemapNameGenerator($this->fs);
    }

    public function testPathGeneration()
    {
        static::assertSame('shop-1/sitemap-1.xml.gz', $this->generator->getSitemapFilename(1));

        $this->fs->write('shop-1/sitemap-1.xml.gz', '');

        static::assertSame('shop-1/sitemap-2.xml.gz', $this->generator->getSitemapFilename(1));

        $this->fs->delete('shop-1/sitemap-1.xml.gz');
    }
}
