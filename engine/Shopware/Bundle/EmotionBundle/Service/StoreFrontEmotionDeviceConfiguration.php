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

namespace Shopware\Bundle\EmotionBundle\Service;

use Shopware\Bundle\StoreFrontBundle\Struct\ShopContextInterface;
use Shopware\Components\Emotion\DeviceConfigurationInterface;

class StoreFrontEmotionDeviceConfiguration implements StoreFrontEmotionDeviceConfigurationInterface
{
    /**
     * @var DeviceConfigurationInterface
     */
    private $deviceConfiguration;

    public function __construct(DeviceConfigurationInterface $deviceConfiguration)
    {
        $this->deviceConfiguration = $deviceConfiguration;
    }

    /**
     * {@inheritdoc}
     */
    public function getCategoryConfiguration($categoryId, ShopContextInterface $context, $withStreams = false)
    {
        $configurations = $this->deviceConfiguration->get($categoryId);

        if (empty($configurations)) {
            return [];
        }

        // filter by shop id
        $configurations = array_filter($configurations, function ($config) use ($context) {
            return empty($config['shopIds']) || in_array($context->getShop()->getId(), $config['shopIds']);
        });

        //no active stream detected? display only emotions without customer stream configuration
        if (empty($context->getActiveCustomerStreamIds()) || $withStreams === false) {
            return array_filter($configurations, function ($config) {
                return $config['customer_stream_ids'] === null;
            });
        }

        //filter emotions which has customer stream configuration for active streams or which has no configuration
        $configurations = array_filter(
            $configurations,
            function (array $config) use ($context) {
                $ids = array_filter(explode('|', $config['customer_stream_ids']));

                return $config['customer_stream_ids'] === null
                    || !empty(array_intersect($context->getActiveCustomerStreamIds(), $ids))
                ;
            }
        );

        //collect emotion replacements
        $replacements = $this->getReplacements($configurations);

        //remove all emotions which replaced by customer stream emotions
        return array_filter(
            $configurations,
            function (array $config) use ($replacements) {
                return !in_array($config['id'], $replacements);
            }
        );
    }

    /**
     * @return array
     */
    private function getReplacements(array $configurations)
    {
        $replacements = [];
        foreach ($configurations as $config) {
            $replacements = array_merge($replacements, explode('|', $config['replacement']));
        }

        return array_filter($replacements);
    }
}
