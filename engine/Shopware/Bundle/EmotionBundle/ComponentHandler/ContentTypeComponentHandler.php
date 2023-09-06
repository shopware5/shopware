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

namespace Shopware\Bundle\EmotionBundle\ComponentHandler;

use Shopware\Bundle\ContentTypeBundle\Services\RepositoryInterface;
use Shopware\Bundle\ContentTypeBundle\Structs\Criteria;
use Shopware\Bundle\EmotionBundle\Struct\Collection\PrepareDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Collection\ResolvedDataCollection;
use Shopware\Bundle\EmotionBundle\Struct\Element;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

class ContentTypeComponentHandler implements ComponentHandlerInterface
{
    public const CONTENT_TYPE_KEY = 'content_type';
    public const MODE_KEY = 'mode';
    public const IDS_KEY = 'ids';
    public const ID_SEPERATOR = '|';
    public const ITEMS_KEY = 'sItems';
    public const TYPE_KEY = 'sType';

    public const MODE_NEWEST = 0;
    public const MODE_RANDOM = 1;
    public const MODE_SELECTED = 2;

    private const COMPONENT_NAME = 'emotion-components-content-type';

    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function supports(Element $element): bool
    {
        return $element->getComponent()->getType() === self::COMPONENT_NAME;
    }

    public function prepare(PrepareDataCollection $collection, Element $element, ShopContextInterface $context): void
    {
    }

    public function handle(ResolvedDataCollection $collection, Element $element, ShopContextInterface $context): void
    {
        $repository = $this->container->get(
            'shopware.bundle.content_type.' . $element->getConfig()->get(self::CONTENT_TYPE_KEY),
            ContainerInterface::NULL_ON_INVALID_REFERENCE
        );
        if (!$repository instanceof RepositoryInterface) {
            return;
        }

        $mode = (int) $element->getConfig()->get(self::MODE_KEY);

        $criteria = new Criteria();
        $criteria->limit = 5;
        $criteria->loadTranslations = true;
        $criteria->loadAssociations = true;

        if ($mode === self::MODE_NEWEST) {
            $criteria->sort = [
                [
                    'property' => 'id',
                    'direction' => 'DESC',
                ],
            ];
        } elseif ($mode === self::MODE_RANDOM) {
            $criteria->sort = [
                [
                    'property' => 'RANDOM',
                ],
            ];
        } elseif ($mode === self::MODE_SELECTED) {
            $criteria->limit = null;
            $criteria->filter = [
                [
                    'property' => 'id',
                    'value' => array_filter(explode(self::ID_SEPERATOR, $element->getConfig()->get(self::IDS_KEY))),
                ],
            ];
        }

        $result = $repository->findAll($criteria);

        $element->getData()->set(self::ITEMS_KEY, $result->items);
        $element->getData()->set(self::TYPE_KEY, json_decode((string) json_encode($result->type), true));
    }
}
