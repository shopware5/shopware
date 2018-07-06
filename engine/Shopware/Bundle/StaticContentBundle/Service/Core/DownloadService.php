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

namespace Shopware\Bundle\StaticContentBundle\Service\Core;

use Enlight_Controller_Front;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\FilesystemInterface;
use Shopware\Bundle\StaticContentBundle\Service\DownloadServiceInterface;

class DownloadService implements DownloadServiceInterface
{
    /**
     * @var Enlight_Controller_Front
     */
    private $front;

    /**
     * @param Enlight_Controller_Front $front
     */
    public function __construct(
        Enlight_Controller_Front $front
    ) {
        $this->front = $front;
    }

    /**
     * {@inheritdoc}
     *
     * @throws FileNotFoundException
     */
    public function send($location, FilesystemInterface $filesystem)
    {
        @set_time_limit(0);
        $this->front->Plugins()->ViewRenderer()->setNoRender();

        $meta = $filesystem->getMetadata($location);
        $mimeType = $filesystem->getMimetype($location) ?: 'application/octet-stream';

        $response = $this->front->Response();
        $response->setHeader('Content-Type', $mimeType);
        $response->setHeader('Content-Disposition', sprintf('attachment; filename="%s"', basename($location)));
        $response->setHeader('Content-Length', $meta['size']);
        $response->setHeader('Content-Transfer-Encoding', 'binary');
        $response->sendHeaders();
        $response->sendResponse();

        $upstream = $filesystem->readStream($location);
        $downstream = fopen('php://output', 'wb');

        while (!feof($upstream)) {
            fwrite($downstream, fread($upstream, 4096));
        }
    }
}
