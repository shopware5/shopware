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

namespace Shopware\Bundle\StoreFrontBundle\Blog;

use Shopware\Bundle\StoreFrontBundle\Context\ShopContextInterface;
use Shopware\Bundle\StoreFrontBundle\Media\MediaServiceInterface;

/**
 * @category  Shopware
 *
 * @copyright Copyright (c) shopware AG (http://www.shopware.de)
 */
class BlogService implements BlogServiceInterface
{
    /**
     * @var BlogGateway
     */
    private $blogGateway;

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    /**
     * @param BlogGateway           $blogGateway
     * @param MediaServiceInterface $mediaService
     */
    public function __construct(BlogGateway $blogGateway, MediaServiceInterface $mediaService)
    {
        $this->blogGateway = $blogGateway;
        $this->mediaService = $mediaService;
    }

    /**
     * {@inheritdoc}
     */
    public function getList(array $ids, ShopContextInterface $context)
    {
        $blogs = $this->blogGateway->getList($ids);

        $this->resolveMedias($blogs, $context);

        return $blogs;
    }

    /**
     * @param Blog[]               $blogs
     * @param ShopContextInterface $context
     */
    private function resolveMedias(array $blogs, ShopContextInterface $context)
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
