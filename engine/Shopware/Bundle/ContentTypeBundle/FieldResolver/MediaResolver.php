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

namespace Shopware\Bundle\ContentTypeBundle\FieldResolver;

use Shopware\Bundle\StoreFrontBundle\Service\ContextServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Service\MediaServiceInterface;
use Shopware\Components\Compatibility\LegacyStructConverter;

class MediaResolver extends AbstractResolver
{
    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @var LegacyStructConverter
     */
    private $structConverter;

    /**
     * @var ContextServiceInterface
     */
    private $contextService;

    public function __construct(MediaServiceInterface $mediaService, LegacyStructConverter $structConverter, ContextServiceInterface $contextService)
    {
        $this->mediaService = $mediaService;
        $this->structConverter = $structConverter;
        $this->contextService = $contextService;
    }

    public function resolve(): void
    {
        $medias = $this->mediaService->getList($this->resolveIds, $this->contextService->getShopContext());

        foreach ($medias as $id => $media) {
            $this->storage[$id] = $this->structConverter->convertMediaStruct($media);
        }

        $this->resolveIds = [];
    }
}
