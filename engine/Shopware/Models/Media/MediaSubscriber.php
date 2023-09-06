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

namespace Shopware\Models\Media;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Exception;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\DependencyInjection\Container;
use SimpleXMLElement;

class MediaSubscriber implements EventSubscriber
{
    private Container $container;

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * {@inheritdoc}
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postLoad,
            Events::prePersist,
        ];
    }

    /**
     * Set meta data on load
     *
     * @return void
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $this->migrateMeta($eventArgs);
    }

    /**
     * Set meta data on save
     *
     * @return void
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->migrateMeta($eventArgs);
    }

    /**
     * Live migration to fill width/height
     *
     * @throws Exception
     */
    private function migrateMeta(LifecycleEventArgs $eventArgs): void
    {
        $media = $eventArgs->getEntity();
        if (!$media instanceof Media) {
            return;
        }

        if (!$this->isFormatSupported($media)) {
            return;
        }

        $mediaService = $this->container->get(MediaServiceInterface::class);

        if ((!$media->getHeight() || !$media->getWidth()) && $mediaService->has($media->getPath())) {
            switch ($media->getType()) {
                case Media::TYPE_IMAGE:
                    $imageSize = getimagesizefromstring((string) $mediaService->read($media->getPath()));
                    if (\is_array($imageSize)) {
                        [$width, $height] = $imageSize;
                        break;
                    }

                    // no break
                case Media::TYPE_VECTOR:
                    if ($media->getExtension() === 'svg'
                        && $xml = simplexml_load_string($mediaService->read($media->getPath()))
                    ) {
                        /** @var SimpleXMLElement|null $attr */
                        $attr = $xml->attributes();

                        if ((int) $attr->width > 0 && (int) $attr->height > 0) {
                            $width = (int) $attr->width;
                            $height = (int) $attr->height;
                        } elseif ($attr->viewBox && \count($size = explode(' ', $attr->viewBox)) === 4) {
                            $width = (int) $size[2];
                            $height = (int) $size[3];
                        }
                    }
            }

            if (!empty($height) && !empty($width)) {
                if ($media->getId()) {
                    $eventArgs->getEntityManager()->getConnection()->executeUpdate(
                        'UPDATE s_media SET width = :width, height = :height WHERE id = :id',
                        [
                            ':width' => $width,
                            ':height' => $height,
                            ':id' => $media->getId(),
                        ]
                    );
                }

                $media->setWidth($width);
                $media->setHeight($height);
            }
        }
    }

    /**
     * Test file for supported types
     */
    private function isFormatSupported(Media $media): bool
    {
        return $media->getType() === Media::TYPE_IMAGE
            || ($media->getType() === Media::TYPE_VECTOR && $media->getExtension() === 'svg');
    }
}
