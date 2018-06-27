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

use PHPUnit\Framework\TestCase;
use Shopware\Bundle\SitemapBundle\Service\SitemapLister;
use Shopware\Bundle\SitemapBundle\Service\SitemapNameGenerator;
use Shopware\Bundle\SitemapBundle\Struct\Sitemap;

class SitemapListenerTest extends TestCase
{
    /**
     * @var SitemapLister
     */
    private $listener;

    /**
     * @var SitemapNameGenerator
     */
    private $generator;

    private $sitemapFolder;

    protected function setUp()
    {
        parent::setUp();

        $this->sitemapFolder = __DIR__ . '/sitemap';
        $this->generator = new SitemapNameGenerator($this->sitemapFolder);
        $this->listener = new SitemapLister($this->sitemapFolder, __DIR__, $this->generator);

        if (!file_exists($this->sitemapFolder)) {
            mkdir($this->sitemapFolder);
        }
    }

    protected function tearDown()
    {
        parent::tearDown();

        array_map('unlink', glob(rtrim($this->sitemapFolder, '/') . '/*'));
        rmdir($this->sitemapFolder);
    }

    public function testListEmptyFolder()
    {
        $this->assertEmpty($this->listener->getSitemaps());
    }

    public function testListWithSitemap()
    {
        $file = $this->generator->getSitemapFilename(1);
        touch($file);

        $sitemaps = $this->listener->getSitemaps();
        $this->assertNotEmpty($sitemaps);

        $this->assertInstanceOf(Sitemap::class, $sitemaps[0]);

        // Subshop specific sitemaps
        $this->assertEmpty($this->listener->getSitemaps(2));
        $this->assertNotEmpty($this->listener->getSitemaps(1));
    }
}
