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

namespace Shopware\Components;

use Enlight\Event\SubscriberInterface;
use Shopware\Models\Config\Value;
use Symfony\Component\HttpKernel\KernelEvents;
use Zend_Cache;

class CacheSubscriber implements SubscriberInterface
{
    private $clearTags = [];

    /**
     * @var \Zend_Cache_Core
     */
    private $cacheManager;

    public function __construct(\Zend_Cache_Core $cacheManager)
    {
        $this->cacheManager = $cacheManager;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            'Shopware\Models\Config\Value::postUpdate' => 'onConfigElement',
            'Shopware\Models\Config\Value::postPersist' => 'onConfigElement',
            'Shopware\Models\Config\Value::postRemove' => 'onConfigElement',
            KernelEvents::TERMINATE => 'onKernelTerminate',
        ];
    }

    public function onConfigElement(\Enlight_Event_EventArgs $args): void
    {
        /** @var \Shopware\Models\Config\Value $entity */
        $entity = $args->get('entity');

        $this->addTagsConfigValue($entity);
    }

    public function onKernelTerminate(\Enlight_Event_EventArgs $args): void
    {
        if (count($this->clearTags) === 0) {
            return;
        }

        $this->cacheManager->clean(Zend_Cache::CLEANING_MODE_MATCHING_ANY_TAG, array_keys($this->clearTags));
        $this->clearTags = [];
    }

    private function addTagsConfigValue(Value $value): void
    {
        if (!$value->getElement()->getFormId() || !$value->getElement()->getForm()->getPlugin()) {
            return;
        }

        $name = strtolower($value->getElement()->getForm()->getPlugin()->getName());

        $this->clearTags[CacheManager::ITEM_TAG_PLUGIN_CONFIG . $name] = true;
    }
}
