<?php
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

namespace Shopware\Tests\Functional\Controllers\Backend;

use Enlight_Controller_Request_RequestTestCase;
use Enlight_Template_Manager;
use Enlight_View_Default;
use PHPUnit\Framework\TestCase;
use Shopware\Tests\Functional\Traits\ContainerTrait;
use Shopware\Tests\Functional\Traits\DatabaseTransactionBehaviour;
use Shopware_Controllers_Backend_MediaManager;

class MediaManagerTest extends TestCase
{
    use ContainerTrait;
    use DatabaseTransactionBehaviour;

    /**
     * Creates a new album,
     * checks if the new album has inherited the parents settings
     * and deletes it afterward
     */
    public function testAlbumInheritance(): void
    {
        $params = [
            'albumID' => '',
            'createThumbnails' => '',
            'iconCls' => 'sprite-target',
            'id' => '',
            'leaf' => false,
            'mediaCount' => '',
            'parentId' => '-11',
            'position' => '',
            'text' => 'PHPUNIT_ALBUM',
            'thumbnailSize' => [],
        ];

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($params);

        $controller = $this->getController();
        $controller->setRequest($request);
        $controller->saveAlbumAction();

        $jsonBody = $controller->View()->getAssign();
        static::assertTrue($jsonBody['success']);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams(['albumId' => '-11']);
        $controller = $this->getController();
        $controller->getAlbumsAction();

        $jsonBody = $controller->View()->getAssign();
        static::assertTrue($jsonBody['success']);

        $parentNode = null;
        foreach ($jsonBody['data'] as $nodes) {
            if ($nodes['id'] === -11) {
                $parentNode = $nodes;
                break;
            }
        }

        $newAlbum = $parentNode['data'][0];

        static::assertEquals($parentNode['thumbnailSize'], $newAlbum['thumbnailSize']);
        static::assertEquals($parentNode['thumbnailHighDpi'], $newAlbum['thumbnailHighDpi']);
        static::assertEquals($parentNode['thumbnailHighDpiQuality'], $newAlbum['thumbnailHighDpiQuality']);
        static::assertEquals($parentNode['thumbnailQuality'], $newAlbum['thumbnailQuality']);
        static::assertEquals($parentNode['createThumbnails'], $newAlbum['createThumbnails']);
        static::assertEquals($parentNode['id'], $newAlbum['parentId']);
        static::assertEquals(1, $newAlbum['leaf']);
        static::assertEquals('sprite-target', $newAlbum['iconCls']);

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('albumID', $newAlbum['id']);
        $controller = $this->getController();
        $controller->setRequest($request);
        $controller->removeAlbumAction();
        $jsonBody = $controller->View()->getAssign();

        static::assertTrue($jsonBody['success']);
    }

    public function testResolveAlbumDataRemoveWhiteSpaces(): void
    {
        $params = [
            'albumID' => '',
            'createThumbnails' => '',
            'iconCls' => 'sprite-target',
            'id' => '',
            'leaf' => false,
            'mediaCount' => '',
            'position' => '',
            'text' => 'PHPUNIT_ALBUM',
            'thumbnailSize' => [
                [
                    'index' => 6,
                    'value' => '50 x 50',
                ],
            ],
        ];

        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParams($params);

        $controller = $this->getController();
        $controller->setRequest($request);
        $controller->saveAlbumAction();

        $jsonBody = $controller->View()->getAssign();
        static::assertTrue($jsonBody['success']);

        $albumId = $jsonBody['data']['id'];
        $db = $this->getContainer()->get('dbal_connection');

        $thumbnailSize = $db->fetchOne('SELECT thumbnail_size FROM s_media_album_settings WHERE albumID = ?;', [$albumId]);

        static::assertStringContainsString('50x50', $thumbnailSize);
        static::assertStringNotContainsString('50 x 50', $thumbnailSize);
    }

    public function testGetAlbumsDoesNotThrowErrorIfNoDataIsAvailable(): void
    {
        $request = new Enlight_Controller_Request_RequestTestCase();
        $request->setParam('albumFilter', 'foo');

        $controller = $this->getController();
        $controller->setRequest($request);
        $controller->getAlbumsAction();
        $results = $controller->View()->getAssign();

        static::assertTrue($results['success']);
        static::assertEmpty($results['data']);
        static::assertSame(0, $results['total']);
    }

    public function getController(): Shopware_Controllers_Backend_MediaManager
    {
        $view = new Enlight_View_Default(new Enlight_Template_Manager());

        $controller = $this->getContainer()->get('shopware_controllers_backend_mediamanager');
        $controller->setView($view);
        $controller->setContainer($this->getContainer());

        return $controller;
    }
}
