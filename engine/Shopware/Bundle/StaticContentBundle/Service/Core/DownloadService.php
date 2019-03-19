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
use League\Flysystem\Adapter\Local;
use League\Flysystem\FileNotFoundException;
use League\Flysystem\Filesystem;
use League\Flysystem\FilesystemInterface;
use Shopware\Bundle\StaticContentBundle\Service\DownloadServiceInterface;
use Shopware\Components\Filesystem\PublicUrlGeneratorInterface;
use Shopware_Components_Config;

class DownloadService implements DownloadServiceInterface
{
    /**
     * @var Enlight_Controller_Front
     */
    private $front;

    /**
     * @var Shopware_Components_Config
     */
    private $config;

    /**
     * @var PublicUrlGeneratorInterface
     */
    private $publicUrlGenerator;

    /**
     * @var string
     */
    private $privateFilesystemRoot;

    /**
     * @var bool
     */
    private $unitTestMode;

    public function __construct(
        Enlight_Controller_Front $front,
        Shopware_Components_Config $config,
        PublicUrlGeneratorInterface $publicUrlGenerator,
        string $privateFilesystemRoot,
        bool $unitTestMode
    ) {
        $this->front = $front;
        $this->config = $config;
        $this->publicUrlGenerator = $publicUrlGenerator;
        $this->privateFilesystemRoot = $privateFilesystemRoot;
        $this->unitTestMode = $unitTestMode;
    }

    /**
     * {@inheritdoc}
     *
     * @throws FileNotFoundException
     */
    public function send(string $location, FilesystemInterface $filesystem): void
    {
        $this->front->Plugins()->ViewRenderer()->setNoRender();
        $downloadStrategy = (int) $this->config->get('esdDownloadStrategy');

        $meta = $filesystem->getMetadata($location);
        $mimeType = $filesystem->getMimetype($location) ?: 'application/octet-stream';

        $response = $this->front->Response();

        if ($this->canServedLocal($filesystem, $downloadStrategy)) {
            $publicUrl = $this->publicUrlGenerator->generateUrl($location);
            $path = parse_url($publicUrl, PHP_URL_PATH);
            switch ($downloadStrategy) {
                case 0:
                    $response->setRedirect($publicUrl);
                    break;
                case 2:
                    $location = $this->privateFilesystemRoot . '/' . $location;
                    $response->headers->set('content-type', 'application/octet-stream');
                    $response->headers->set('content-disposition', sprintf('attachment; filename="%s"', basename($location)));
                    $response->headers->set('x-sendfile', $location);
                    break;
                case 3:
                    $response->headers->set('content-type', 'application/octet-stream');
                    $response->headers->set('content-disposition', sprintf('attachment; filename="%s"', basename($location)));
                    $response->headers->set('x-accel-redirect', $path);
                    break;
            }

            return;
        }

        @set_time_limit(0);

        $response->headers->set('content-type', $mimeType);
        $response->headers->set('content-disposition', sprintf('attachment; filename="%s"', basename($location)));
        $response->headers->set('content-length', $meta['size']);
        $response->headers->set('content-transfer-encoding', 'binary');
        $response->sendHeaders();

        $upstream = $filesystem->readStream($location);
        $downstream = fopen('php://output', 'wb');

        if (!$this->unitTestMode) {
            ob_end_clean();
        }

        while (!feof($upstream)) {
            fwrite($downstream, fread($upstream, 4096));
            flush();
        }
    }

    private function canServedLocal(FilesystemInterface $filesystem, int $downloadStrategy): bool
    {
        return $filesystem instanceof Filesystem && $filesystem->getAdapter() instanceof Local && in_array($downloadStrategy, [0, 2, 3], true);
    }
}
