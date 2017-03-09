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

namespace Shopware\Bundle\StoreFrontBundle\Service\Core;

use Shopware\Bundle\StoreFrontBundle\Gateway;
use Shopware\Bundle\StoreFrontBundle\Service;
use Shopware\Bundle\StoreFrontBundle\Struct;
use Shopware\Bundle\StoreFrontBundle\Struct\Blog\Blog;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class BlogService implements Service\BlogServiceInterface
{
    /**
     * @var Gateway\BlogGateway
     */
    private $blogGateway;

    /**
     * @var Service\MediaServiceInterface
     */
    private $mediaService;

    /**
     * @param Gateway\BlogGateway           $blogGateway
     * @param Service\MediaServiceInterface $mediaService
     */
    public function __construct(Gateway\BlogGateway $blogGateway, Service\MediaServiceInterface $mediaService)
    {
        $this->blogGateway = $blogGateway;
        $this->mediaService = $mediaService;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $ids, Struct\ShopContextInterface $context)
    {
        $blogs = $this->blogGateway->getList($ids);

        $this->resolveMedias($blogs, $context);

        return $blogs;
    }

    /**
     * @param Blog[]                      $blogs
     * @param Struct\ShopContextInterface $context
     */
    private function resolveMedias(array $blogs, Struct\ShopContextInterface $context)
    {
        $mediaIds = [];
        foreach ($blogs as $blog) {
            $mediaIds[] = $blog->getMediaIds();
        }

        $mediaIds = array_keys(array_flip(array_merge(...$mediaIds)));
        $mediaList = $this->mediaService->getList($mediaIds, $context);

        foreach ($blogs as $blog) {
            $medias = [];

            foreach ($blog->getMediaIds() as $mediaId) {
                if (array_key_exists($mediaId, $mediaList)) {
                    $medias[$mediaId] = $mediaList[$mediaId];
                }
            }

            $blog->setMedias($medias);
        }
    }
}
