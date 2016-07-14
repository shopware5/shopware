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

namespace Shopware\Models\Media;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use Shopware\Components\DependencyInjection\Container;

/**
 * @category  Shopware
 * @package   Shopware\Models\Media
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class MediaSubscriber implements EventSubscriber
{
    /**
     * @var Container
     */
    private $container;

    /**
     * @param Container $container
     */
    public function __construct($container)
    {
        $this->container = $container;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return array
     */
    public function getSubscribedEvents()
    {
        return [
            Events::postLoad,
            Events::prePersist
        ];
    }

    /**
     * Live migration to fill width/height
     *
     * @param LifecycleEventArgs $eventArgs
     * @throws \Exception
     */
    private function migrateMeta(LifecycleEventArgs $eventArgs)
    {
        $media = $eventArgs->getEntity();

        if (!($media instanceof Media)) {
            return;
        }

        if ($media->getType() !== Media::TYPE_IMAGE) {
            return;
        }

        $mediaService = $this->container->get('shopware_media.media_service');

        if ((!$media->getHeight() || !$media->getWidth()) && $mediaService->has($media->getPath())) {
            list($width, $height) = getimagesizefromstring($mediaService->read($media->getPath()));

            if ($media->getId()) {
                $eventArgs->getEntityManager()->getConnection()->executeUpdate(
                    'UPDATE s_media SET width = :width, height = :height WHERE id = :id',
                    [
                        ':width' => $width,
                        ':height' => $height,
                        ':id' => $media->getId()
                    ]
                );
            }

            $media->setWidth($width);
            $media->setHeight($height);
        }
    }

    /**
     * Set meta data on load
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function postLoad(LifecycleEventArgs $eventArgs)
    {
        $this->migrateMeta($eventArgs);
    }

    /**
     * Set meta data on save
     *
     * @param LifecycleEventArgs $eventArgs
     */
    public function prePersist(LifecycleEventArgs $eventArgs)
    {
        $this->migrateMeta($eventArgs);
    }
}
