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

namespace Shopware\Bundle\AttributeBundle\Repository\Reader;

use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Components\Model\ModelManager;
use Shopware\Models\Media\Media;

class MediaReader extends GenericReader
{
    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @param string $entity
     */
    public function __construct(
        $entity,
        ModelManager $entityManager,
        MediaServiceInterface $mediaService
    ) {
        parent::__construct($entity, $entityManager);
        $this->mediaService = $mediaService;
    }

    public function getList($identifiers)
    {
        $list = parent::getList($identifiers);
        foreach ($list as &$media) {
            $media['thumbnail'] = $this->getMediaThumbnail($media);
        }

        return $list;
    }

    /**
     * @param int|string $identifier
     *
     * @return array
     */
    public function get($identifier)
    {
        $media = parent::get($identifier);
        $media['thumbnail'] = $this->getMediaThumbnail($media);

        return $media;
    }

    /**
     * @param array $media
     *
     * @return string|null
     */
    private function getMediaThumbnail($media)
    {
        if ($media['type'] === Media::TYPE_IMAGE) {
            $media['path'] = str_replace($media['name'], $media['name'] . '_140x140', $media['path']);
            $media['path'] = str_replace('media/image/', 'media/image/thumbnail/', $media['path']);
        }

        return $this->mediaService->getUrl($media['path']);
    }
}
