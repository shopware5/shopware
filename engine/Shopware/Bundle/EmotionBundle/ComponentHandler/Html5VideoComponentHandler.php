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

namespace Shopware\Bundle\EmotionBundle\ComponentHandler;

use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\MediaBundle\MediaServiceInterface;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;

class Html5VideoComponentHandler implements ComponentHandlerInterface
{
    const LEGACY_CONVERT_FUNCTION = 'getHtml5Video';
    const COMPONENT_NAME = 'emotion-components-html-video';

    /**
     * @var MediaServiceInterface
     */
    private $mediaService;

    public function __construct(MediaServiceInterface $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    /**
     * @return bool
     */
    public function supports(Element $element)
    {
        return $element->getComponent()->getType() === self::COMPONENT_NAME
            || $element->getComponent()->getConvertFunction() === self::LEGACY_CONVERT_FUNCTION;
    }

    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context)
    {
    }

    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context)
    {
        $mediaFields = ['webm_video', 'ogg_video', 'h264_video', 'fallback_picture'];

        foreach ($mediaFields as $field) {
            $value = $element->getConfig()->get($field);

            if (!preg_match('#^media/*#i', $value)) {
                continue;
            }

            $element->getConfig()->set(
                $field,
                $this->mediaService->getUrl($value)
            );
        }
    }
}
