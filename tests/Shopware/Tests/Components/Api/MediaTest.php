<?php
/**
 * Shopware 4.0
 * Copyright © 2013 shopware AG
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

/**
 * @category  Shopware
 * @package   Shopware\Tests
 * @copyright Copyright (c) 2013, shopware AG (http://www.shopware.de)
 */
class Shopware_Tests_Components_Api_MediaTest extends Shopware_Tests_Components_Api_TestCase
{
    /**
     * @var \Shopware\Components\Api\Resource\Media
     */
    protected $resource;

    /**
     * @return \Shopware\Components\Api\Resource\Media
     */
    public function createResource()
    {
        return new Shopware\Components\Api\Resource\Media();
    }

    public function testUploadName()
    {
        $data = $this->getSimpleTestData();
        $source = __DIR__ . '/fixtures/test-bild.jpg';
        $dest = __DIR__ . '/fixtures/test-bild-used.jpg';

        //copy image to execute test case multiple times.
        unlink($dest);
        copy($source, $dest);

        $data['file'] = $dest;
        $path = Shopware()->DocPath('media_image') . '/test-bild-used.jpg';
        unlink($path);

        $media = $this->resource->create($data);
        $this->assertFileExists($path);
    }


    protected function getSimpleTestData()
    {
        return array(
            'album' => -1,
            'description' => 'Test description'
        );
    }

}
